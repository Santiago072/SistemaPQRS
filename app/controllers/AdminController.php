<?php
/**
 * AdminController.php — Controlador del panel de administración
 *
 * Principio SRP: cada método maneja una sola acción del administrador.
 * Principio DIP: depende de PqrsModel y EmailService, no de MySQLi directo.
 * Principio OCP: agregar nuevas rutas admin no modifica el enrutador.
 */

use App\Models\PqrsModel;
use App\Services\EmailService;

class AdminController
{
    private PqrsModel $pqrsModel;

    public function __construct()
    {
        $this->pqrsModel = new PqrsModel();
    }

    // ─── Vistas simples (solo cargan el HTML) ─────────────────────────────────

    public function actualizar_perfil(): void  { require_once __DIR__ . '/../views/admin/actualizar_perfil.php'; }
    public function alertas(): void            { require_once __DIR__ . '/../views/admin/alertas.php'; }
    public function configuracion(): void      { require_once __DIR__ . '/../views/admin/configuracion.php'; }
    public function dashboard(): void          { require_once __DIR__ . '/../views/admin/dashboard_admin.php'; }
    public function exportar_excel(): void     { require_once __DIR__ . '/../views/admin/exportar_excel.php'; }
    public function exportar_pdf(): void       { require_once __DIR__ . '/../views/admin/exportar_pdf.php'; }
    public function login(): void              { require_once __DIR__ . '/../views/admin/login.php'; }
    public function logout(): void             { require_once __DIR__ . '/../views/admin/logout.php'; }
    public function pqrs(): void               { require_once __DIR__ . '/../views/admin/pqrs.php'; }
    public function pqrs_historial(): void     { require_once __DIR__ . '/../views/admin/pqrs_historial.php'; }
    public function pqrs_responder(): void     
    {
        require_once __DIR__ . '/../../config/conexion.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/pqrs');
            exit;
        }
        
        $pqrs = $this->pqrsModel->obtenerPorId($id);
        if (!$pqrs) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/pqrs&error=not_found');
            exit;
        }
        
        // La vista usa la variable $pqrs (array) y las claves correspondientes
        require_once __DIR__ . '/../views/admin/pqrs_responder.php'; 
    }
    public function pqrs_ver(): void           { require_once __DIR__ . '/../views/admin/pqrs_ver.php'; }
    public function recuperar(): void          { require_once __DIR__ . '/../views/admin/recuperar_contrasena.php'; }
    public function recuperar_contrasena(): void { require_once __DIR__ . '/../views/admin/recuperar_contrasena.php'; }
    public function reportes(): void           { require_once __DIR__ . '/../views/admin/reportes.php'; }
    public function restablecer_contrasena(): void { require_once __DIR__ . '/../views/admin/restablecer_contrasena.php'; }

    // ─── Acción: Cambiar estado de una PQRS ──────────────────────────────────
    // Movida desde app/views/admin/pqrs_cambiar_estado.php (que era una vista con lógica)

    public function pqrs_cambiar_estado(): void
    {
        require_once __DIR__ . '/../../config/conexion.php';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Solo acepta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/pqrs');
            exit;
        }

        $pqrsId      = (int) ($_POST['pqrs_id']     ?? 0);
        $nuevoEstado = trim($_POST['nuevo_estado']   ?? '');
        $comentario  = trim($_POST['comentario']     ?? '');
        $redirect    = $_POST['redirect']            ?? (BASE_PATH . 'index.php?ruta=admin/pqrs');
        $adminId     = (int) ($_SESSION['admin_id'] ?? 0);

        $estadosValidos = ['PENDIENTE', 'EN_PROCESO', 'RESUELTO', 'RECHAZADO'];

        if (!$pqrsId || !in_array($nuevoEstado, $estadosValidos, true)) {
            header("Location: {$redirect}&error=invalid");
            exit;
        }

        // Obtener estado anterior antes de cambiar
        $pqrs = $this->pqrsModel->obtenerPorId($pqrsId);
        if (!$pqrs) {
            header("Location: {$redirect}&error=not_found");
            exit;
        }
        $estadoAnterior = $pqrs['estado'];

        // Intentar cambio de estado con validación de transiciones
        if (!$this->pqrsModel->cambiarEstado($pqrsId, $nuevoEstado)) {
            header("Location: {$redirect}&error=invalid_transition");
            exit;
        }

        // Registrar en historial
        $descripcion = "Estado cambiado de '{$estadoAnterior}' a '{$nuevoEstado}'";
        if ($comentario) {
            $descripcion .= ". Comentario: {$comentario}";
        }
        $this->pqrsModel->registrarAccion(
            $pqrsId, $adminId, 'CAMBIO_ESTADO', $descripcion, $estadoAnterior, $nuevoEstado
        );

        $separator = strpos($redirect, '?') !== false ? '&' : '?';
        header("Location: {$redirect}{$separator}success=estado_actualizado");
        exit;
    }

    // ─── Acción: Enviar respuesta al ciudadano ────────────────────────────────
    // La lógica de negocio fue extraída desde pqrs_responder.php (que era vista con lógica)

    public function guardar_respuesta(): void
    {
        require_once __DIR__ . '/../../config/conexion.php';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/pqrs');
            exit;
        }

        $pqrsId         = (int) ($_POST['pqrs_id']       ?? 0);
        $contenido      = trim($_POST['contenido']        ?? '');
        $nuevoEstado    = trim($_POST['nuevo_estado']     ?? '');
        $esVisible      = isset($_POST['es_visible_publico']) ? 1 : 0;
        $adminId        = (int) ($_SESSION['admin_id']   ?? 0);

        if (!$pqrsId || empty($contenido)) {
            header('Location: ' . BASE_PATH . "index.php?ruta=admin/pqrs_responder&id={$pqrsId}&error=campos_vacios");
            exit;
        }

        // Guardar respuesta visible al ciudadano
        if ($esVisible) {
            $this->pqrsModel->guardarRespuesta($pqrsId, $contenido, $adminId);
        }

        // Cambiar estado si se seleccionó uno
        $estadoAnterior = '';
        if (!empty($nuevoEstado)) {
            $pqrsActual = $this->pqrsModel->obtenerPorId($pqrsId);
            $estadoAnterior = $pqrsActual['estado'] ?? '';
            $this->pqrsModel->cambiarEstado($pqrsId, $nuevoEstado);
        }

        // Registrar en historial
        $descRespuesta = 'Respuesta enviada' . ($esVisible ? ' (publica)' : ' (interna)');
        $this->pqrsModel->registrarAccion($pqrsId, $adminId, 'RESPUESTA', $descRespuesta, $estadoAnterior, $nuevoEstado);

        if (!empty($nuevoEstado)) {
            $this->pqrsModel->registrarAccion($pqrsId, $adminId, 'CAMBIO_ESTADO', "Estado cambiado a: {$nuevoEstado}", $estadoAnterior, $nuevoEstado);
        }

        // Enviar correo de notificación al ciudadano
        $correoNotificado = false;
        if ($esVisible) {
            $pqrs = $this->pqrsModel->obtenerPorId($pqrsId);
            if ($pqrs) {
                $correoDestino = null;
                if (($pqrs['tipo_persona'] ?? '') === 'natural' && !empty($pqrs['correo_electronico'])) {
                    $correoDestino = $pqrs['correo_electronico'];
                } elseif (($pqrs['tipo_persona'] ?? '') === 'juridica' && !empty($pqrs['correo_corporativo'])) {
                    $correoDestino = $pqrs['correo_corporativo'];
                }

                if (!empty($correoDestino)) {
                    $estadoFinal   = !empty($nuevoEstado) ? $nuevoEstado : ($pqrs['estado'] ?? 'PENDIENTE');
                    $nombreCiudadano = $pqrs['nombre_completo'] ?? $pqrs['nombre_representante'] ?? '';
                    try {
                        $emailService     = new EmailService();
                        $correoNotificado = $emailService->enviarRespuestaAdministrador(
                            $correoDestino,
                            $nombreCiudadano,
                            $pqrs['codigo_radicado'],
                            $pqrs['tipo_solicitud'],
                            $pqrs['asunto'],
                            $contenido,
                            $estadoFinal,
                            $_SERVER['HTTP_HOST']
                        );
                    } catch (\RuntimeException $e) {
                        error_log('EmailService guardar_respuesta: ' . $e->getMessage());
                    }
                }
            }
        }

        $_SESSION['respuesta_exito']         = 'Respuesta registrada exitosamente.'
            . ($correoNotificado ? ' El ciudadano ha sido notificado por correo.' : '');
        $_SESSION['respuesta_exito_pqrs_id'] = $pqrsId;

        header('Location: ' . BASE_PATH . "index.php?ruta=admin/pqrs_ver&id={$pqrsId}&success=respuesta_enviada");
        exit;
    }
}
