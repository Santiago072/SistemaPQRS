<?php
namespace App\Controllers\Admin;

use App\Models\PqrsModel;
use App\Services\EmailService;

class PqrsController
{
    private PqrsModel $pqrsModel;

    public function __construct(PqrsModel $pqrsModel)
    {
        $this->pqrsModel = $pqrsModel;
    }

    public function pqrs(): void               
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $filtros = [
            'estado'       => $_GET['estado'] ?? '',
            'tipo'         => $_GET['tipo'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin'    => $_GET['fecha_fin'] ?? '',
            'orden'        => $_GET['orden'] ?? 'recientes',
            'busqueda'     => $_GET['busqueda'] ?? ''
        ];

        $pagina = max(1, intval($_GET['pagina'] ?? 1));
        $porPagina = 15;
        $offset = ($pagina - 1) * $porPagina;

        $resultado = $this->pqrsModel->obtenerListadoPaginado($filtros, $pagina, $porPagina);
        $total_registros = $resultado['total_registros'];
        $total_paginas = $resultado['total_paginas'];
        $pqrs_list = $resultado['data'];
        $estadisticas = $resultado['estadisticas'];

        $alertas = $this->pqrsModel->obtenerAlertasVencimiento();

        $filtro_estado = $filtros['estado'];
        $filtro_tipo = $filtros['tipo'];
        $filtro_fecha_inicio = $filtros['fecha_inicio'];
        $filtro_fecha_fin = $filtros['fecha_fin'];
        $orden = $filtros['orden'];
        $busqueda = $filtros['busqueda'];

        require_once __DIR__ . '/../../../app/views/admin/pqrs.php'; 
    }

    public function pqrs_ver(): void           
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/pqrs');
            exit;
        }

        $pqrs = $this->pqrsModel->obtenerDetalleCompleto($id);
        if (!$pqrs) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/pqrs&error=not_found');
            exit;
        }

        $historial = $this->pqrsModel->obtenerHistorialAcciones($id);

        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $this->pqrsModel->registrarAccion($id, $adminId, 'VISUALIZACION', "Vista del detalle de PQRS", $pqrs['estado'], $pqrs['estado']);

        require_once __DIR__ . '/../../../app/views/admin/pqrs_ver.php'; 
    }

    public function pqrs_cambiar_estado(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        $pqrs = $this->pqrsModel->obtenerPorId($pqrsId);
        if (!$pqrs) {
            header("Location: {$redirect}&error=not_found");
            exit;
        }
        $estadoAnterior = $pqrs['estado'];

        if (!$this->pqrsModel->cambiarEstado($pqrsId, $nuevoEstado)) {
            header("Location: {$redirect}&error=invalid_transition");
            exit;
        }

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

    public function pqrs_responder(): void     
    {
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
        
        require_once __DIR__ . '/../../../app/views/admin/pqrs_responder.php'; 
    }

    public function guardar_respuesta(): void
    {
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

        if ($esVisible) {
            $this->pqrsModel->guardarRespuesta($pqrsId, $contenido, $adminId);
        }

        $estadoAnterior = '';
        if (!empty($nuevoEstado)) {
            $pqrsActual = $this->pqrsModel->obtenerPorId($pqrsId);
            $estadoAnterior = $pqrsActual['estado'] ?? '';
            $this->pqrsModel->cambiarEstado($pqrsId, $nuevoEstado);
        }

        $descRespuesta = 'Respuesta enviada' . ($esVisible ? ' (publica)' : ' (interna)');
        $this->pqrsModel->registrarAccion($pqrsId, $adminId, 'RESPUESTA', $descRespuesta, $estadoAnterior, $nuevoEstado);

        if (!empty($nuevoEstado)) {
            $this->pqrsModel->registrarAccion($pqrsId, $adminId, 'CAMBIO_ESTADO', "Estado cambiado a: {$nuevoEstado}", $estadoAnterior, $nuevoEstado);
        }

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

    public function pqrs_historial(): void     
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/pqrs');
            exit;
        }

        $pqrs = $this->pqrsModel->obtenerPorId($id);
        if (!$pqrs) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/pqrs&error=not_found');
            exit;
        }

        $historial = $this->pqrsModel->obtenerHistorialAcciones($id);

        require_once __DIR__ . '/../../../app/views/admin/pqrs_historial.php'; 
    }

    public function alertas(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $resultado = $this->pqrsModel->obtenerAlertasDetalladas();
        $alertas_critico = $resultado['critico'];
        $alertas_urgente = $resultado['urgente'];
        $alertas_moderado = $resultado['moderado'];
        $alertas_vencidas = $resultado['vencidas'];

        require_once __DIR__ . '/../../../app/views/admin/alertas.php';
    }
}
