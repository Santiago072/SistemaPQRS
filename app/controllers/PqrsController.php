<?php
/**
 * PqrsController.php — Controlador de operaciones ciudadanas PQRS
 *
 * Principio SRP: coordina el flujo entre Modelos y Vistas.
 * Principio DIP: depende de abstracciones (PqrsModel, UsuarioModel, EmailService).
 * Principio OCP: agregar nuevas acciones no modifica las existentes.
 */

use App\Models\PqrsModel;
use App\Models\UsuarioModel;
use App\Services\EmailService;

class PqrsController
{
    private PqrsModel    $pqrsModel;
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        $this->pqrsModel    = new PqrsModel();
        $this->usuarioModel = new UsuarioModel();
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
            $datosUsuario['nombre_completo']     = trim($_POST['nombre']           ?? '') ?: null;
            $datosUsuario['documento_identidad'] = trim($_POST['numero_documento'] ?? '') ?: null;
            $datosUsuario['tipo_documento']      = trim($_POST['tipo_documento']   ?? '') ?: null;
            $datosUsuario['correo_electronico']  = trim($_POST['correo']           ?? '') ?: null;
            $datosUsuario['telefono']            = trim($_POST['telefono']         ?? '') ?: null;
        } elseif ($tipoPersona === 'juridica') {
            $datosUsuario['razon_social']         = trim($_POST['razon_social']       ?? '') ?: null;
            $datosUsuario['nit']                  = trim($_POST['nit']                ?? '') ?: null;
            $datosUsuario['nombre_representante'] = trim($_POST['representante']      ?? '') ?: null;
            $datosUsuario['correo_corporativo']   = trim($_POST['correo_corporativo'] ?? '') ?: null;
            $datosUsuario['telefono']             = trim($_POST['telefono_juridica']  ?? '') ?: null;
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
        require_once __DIR__ . '/../views/pqrs/confirmacion.php';
    }

    // ─── Vista: Consulta de estado ────────────────────────────────────────────

    public function consulta(): void
    {
        require_once __DIR__ . '/../views/pqrs/consulta.php';
    }
}
