<?php
/**
 * PqrsController.php — Controlador de operaciones ciudadanas PQRS
 *
 * Principio SRP: coordina el flujo entre Modelos y Vistas.
 * Principio DIP: depende de abstracciones (PqrsModel, UsuarioModel, EmailService).
 * Principio OCP: agregar nuevas acciones no modifica las existentes.
 */

namespace App\Controllers;

use App\Models\PqrsModel;
use App\Models\UsuarioModel;
use App\Services\EmailService;

class PqrsController
{
    private PqrsModel    $pqrsModel;
    private UsuarioModel $usuarioModel;

    public function __construct(PqrsModel $pqrsModel, UsuarioModel $usuarioModel)
    {
        $this->pqrsModel    = $pqrsModel;
        $this->usuarioModel = $usuarioModel;
    }

    // ─── Vista: Selección de tipo ─────────────────────────────────────────────

    public function tipos(): void
    {
        require_once __DIR__ . '/../views/pqrs/tipos.php';
    }

    // ─── Vista: Formulario de radicación ─────────────────────────────────────

    public function formulario(): void
    {
        $tipoPQRS = isset($_GET['tipo_pqrs']) ? htmlspecialchars($_GET['tipo_pqrs']) : 'peticion';
        $nombresTipos = [
            'peticion'   => 'Peticion',
            'queja'      => 'Queja',
            'reclamo'    => 'Reclamo',
            'sugerencia' => 'Sugerencia',
            'denuncia'   => 'Denuncia',
        ];
        $nombreTipo = $nombresTipos[$tipoPQRS] ?? 'Peticion';
        require_once __DIR__ . '/../views/pqrs/formulario.php';
    }

    // ─── Acción: Radicar nueva PQRS ──────────────────────────────────────────

    public function radicar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . 'index.php?ruta=pqrs/tipos');
            exit;
        }

        // --- RATE LIMITING (Límite de Tasa) ---
        $cooldownSeconds = 120; // 2 minutos entre radicaciones
        if (isset($_SESSION['ultima_pqrs']) && (time() - $_SESSION['ultima_pqrs']) < $cooldownSeconds) {
            $faltan = $cooldownSeconds - (time() - $_SESSION['ultima_pqrs']);
            $tipoPqrsRetorno = urlencode($_POST['tipo_pqrs'] ?? 'peticion');
            header('Location: ' . BASE_PATH . 'index.php?ruta=pqrs/formulario&error=rate_limit&faltan=' . $faltan . '&tipo_pqrs=' . $tipoPqrsRetorno);
            exit;
        }

        // 1. Validar y sanitizar datos básicos
        $tiposPqrsValidos    = ['peticion', 'queja', 'reclamo', 'sugerencia', 'denuncia'];
        $tiposPersonaValidos = ['natural', 'juridica', 'anonima'];

        $tipoPqrs    = in_array($_POST['tipo_pqrs']    ?? '', $tiposPqrsValidos,    true) ? $_POST['tipo_pqrs']    : 'peticion';
        $tipoPersona = in_array($_POST['tipo_persona'] ?? '', $tiposPersonaValidos, true) ? $_POST['tipo_persona'] : 'natural';
        $asunto      = mb_substr(trim($_POST['asunto']      ?? ''), 0, 250);
        $descripcion = mb_substr(trim($_POST['descripcion'] ?? ''), 0, 5000);
        $notificar   = ($tipoPersona !== 'anonima' && isset($_POST['notificar_correo'])) ? 1 : 0;

        if (empty($asunto) || empty($descripcion)) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=pqrs/formulario&tipo_pqrs=' . urlencode($tipoPqrs) . '&error=campos_vacios');
            exit;
        }

        // 2. Construir datos del usuario según tipo de persona
        $datosUsuario = ['tipo_persona' => $tipoPersona];
        if ($tipoPersona === 'natural') {
            $datosUsuario['nombre_completo']     = mb_substr(trim($_POST['nombre']           ?? ''), 0, 150) ?: null;
            $datosUsuario['documento_identidad'] = mb_substr(trim($_POST['numero_documento'] ?? ''), 0, 50)  ?: null;
            $datosUsuario['tipo_documento']      = mb_substr(trim($_POST['tipo_documento']   ?? ''), 0, 20)  ?: null;
            $datosUsuario['correo_electronico']  = mb_substr(trim($_POST['correo']           ?? ''), 0, 150) ?: null;
            $datosUsuario['telefono']            = mb_substr(trim($_POST['telefono']         ?? ''), 0, 50)  ?: null;
        } elseif ($tipoPersona === 'juridica') {
            $datosUsuario['razon_social']         = mb_substr(trim($_POST['razon_social']       ?? ''), 0, 150) ?: null;
            $datosUsuario['nit']                  = mb_substr(trim($_POST['nit']                ?? ''), 0, 50)  ?: null;
            $datosUsuario['nombre_representante'] = mb_substr(trim($_POST['representante']      ?? ''), 0, 150) ?: null;
            $datosUsuario['correo_corporativo']   = mb_substr(trim($_POST['correo_corporativo'] ?? ''), 0, 150) ?: null;
            $datosUsuario['telefono']             = mb_substr(trim($_POST['telefono_juridica']  ?? ''), 0, 50)  ?: null;
            $datosUsuario['correo_electronico']   = $datosUsuario['correo_corporativo'];
            $datosUsuario['nombre_completo']      = $datosUsuario['nombre_representante'];
        }

        // 3. Insertar usuario
        $usuarioId = $this->usuarioModel->crear($datosUsuario);

        // 4. Generar código y calcular vencimiento
        $codigoRadicado  = $this->pqrsModel->generarCodigoRadicado();
        $diasVencimiento = $this->pqrsModel->obtenerDiasVencimiento($tipoPqrs);
        $fechaVencimiento = date('Y-m-d', strtotime("+{$diasVencimiento} days"));

        // 5. Manejar archivo adjunto
        $archivoAdjunto = null;
        if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
            $nombreArchivo = time() . '_' . basename($_FILES['adjunto']['name']);
            $dirDestino    = dirname(__DIR__, 2) . '/uploads';
            if (!is_dir($dirDestino)) {
                @mkdir($dirDestino, 0755, true);
            }
            if (@move_uploaded_file($_FILES['adjunto']['tmp_name'], $dirDestino . '/' . $nombreArchivo)) {
                $archivoAdjunto = $nombreArchivo;
            }
        }

        // 6. Insertar PQRS
        $pqrsId = $this->pqrsModel->crear([
            'codigo_radicado'    => $codigoRadicado,
            'tipo_solicitud'     => $tipoPqrs,
            'asunto'             => $asunto,
            'descripcion'        => $descripcion,
            'archivo_adjunto'    => $archivoAdjunto,
            'fecha_vencimiento'  => $fechaVencimiento,
            'desea_notificacion' => $notificar,
            'usuario_id'         => $usuarioId,
        ]);

        // Registrar hora de la radicación exitosa para el Rate Limit
        $_SESSION['ultima_pqrs'] = time();

        // 7. Enviar correo de confirmación (EmailService — SRP)
        $correoEnviado = false;
        $correoDestino = $datosUsuario['correo_electronico'] ?? $datosUsuario['correo_corporativo'] ?? null;

        if ($notificar && !empty($correoDestino)) {
            try {
                $emailService  = new EmailService();
                $correoEnviado = $emailService->enviarConfirmacionRadicacion(
                    $correoDestino,
                    $datosUsuario['nombre_completo'] ?? '',
                    $codigoRadicado,
                    $tipoPqrs,
                    $asunto,
                    date('d/m/Y', strtotime($fechaVencimiento)),
                    $_SERVER['HTTP_HOST']
                );
            } catch (\RuntimeException $e) {
                // SMTP no configurado — no es error crítico, continuar sin correo
                error_log('EmailService: ' . $e->getMessage());
            }
        }

        $_SESSION['correo_enviado'] = $correoEnviado;
        header('Location: ' . BASE_PATH . "index.php?ruta=pqrs/confirmacion&id={$pqrsId}");
        exit;
    }

    // ─── Vista: Confirmación ──────────────────────────────────────────────────

    public function confirmacion(): void
    {
        $pqrsId = (int) ($_GET['id'] ?? 0);

        if ($pqrsId === 0) {
            header('Location: ' . BASE_PATH . 'index.php');
            exit();
        }

        $pqrs = $this->pqrsModel->obtenerPorId($pqrsId);

        if (!$pqrs) {
            header('Location: ' . BASE_PATH . 'index.php');
            exit();
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $correoEnviado = isset($_SESSION['correo_enviado']) ? $_SESSION['correo_enviado'] : false;
        unset($_SESSION['correo_enviado']);

        require_once __DIR__ . '/../views/pqrs/confirmacion.php';
    }

    // ─── Vista: Consulta de estado ────────────────────────────────────────────

    public function consulta(): void
    {
        $resultados    = [];
        $error         = null;
        $busqueda      = null;
        $tipoBusqueda  = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['codigo'])) {
            $codigo = trim($_POST['codigo'] ?? $_GET['codigo'] ?? '');
            $correo = trim($_POST['correo'] ?? '');

            if (!empty($codigo)) {
                $codigo_upper = strtoupper($codigo);
                $pqrs = $this->pqrsModel->obtenerPorCodigo($codigo_upper);
                
                if ($pqrs) {
                    $resultados[]  = $pqrs;
                    $tipoBusqueda  = 'codigo';
                    $busqueda      = $codigo_upper;
                } else {
                    $error = "No se encontró ninguna solicitud con el código <strong>" . htmlspecialchars($codigo_upper) . "</strong>. Verifique que el código sea correcto.";
                }
            } elseif (!empty($correo)) {
                $correo_lower = strtolower($correo);
                $listado = $this->pqrsModel->obtenerListadoPorCorreo($correo_lower);

                if (!empty($listado)) {
                    $resultados = $listado;
                    $tipoBusqueda = 'correo';
                    $busqueda     = $correo_lower;
                } else {
                    $error = "No se encontraron solicitudes asociadas al correo <strong>" . htmlspecialchars($correo_lower) . "</strong>.";
                }
            } else {
                $error = 'Ingrese un código de radicado o un correo electrónico para buscar.';
            }
        }

        require_once __DIR__ . '/../views/pqrs/consulta.php';
    }
}
