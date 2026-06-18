<?php
/**
 * AdminController.php — Controlador del panel de administración
 *
 * Principio SRP: cada método maneja una sola acción del administrador.
 * Principio DIP: depende de PqrsModel y EmailService, no de MySQLi directo.
 * Principio OCP: agregar nuevas rutas admin no modifica el enrutador.
 */

use App\Models\PqrsModel;
use App\Models\AdminModel;
use App\Models\ConfiguracionModel;
use App\Services\EmailService;

class AdminController
{
    private PqrsModel $pqrsModel;
    private AdminModel $adminModel;
    private ConfiguracionModel $configuracionModel;

    public function __construct()
    {
        $this->pqrsModel = new PqrsModel();
        $this->adminModel = new AdminModel();
        $this->configuracionModel = new ConfiguracionModel();
    }

    // ─── Vistas simples (solo cargan el HTML) ─────────────────────────────────

    public function actualizar_perfil(): void  
    { 
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido.']);
            exit();
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        if (!$adminId) {
            echo json_encode(['ok' => false, 'msg' => 'No autorizado.']);
            exit();
        }

        $nombre  = trim($_POST['nombre_completo'] ?? '');
        $correo  = trim($_POST['correo_electronico'] ?? '');
        $passActual = $_POST['password_actual'] ?? '';
        $passNueva  = $_POST['password_nueva'] ?? '';
        $passConfirm = $_POST['password_confirmar'] ?? '';

        if (empty($nombre) || empty($correo)) {
            echo json_encode(['ok' => false, 'msg' => 'Nombre y correo son obligatorios.']);
            exit();
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'msg' => 'Correo electrónico no válido.']);
            exit();
        }

        $hashNueva = null;
        if (!empty($passNueva)) {
            if (empty($passActual)) {
                echo json_encode(['ok' => false, 'msg' => 'Debe ingresar su contraseña actual para cambiarla.']);
                exit();
            }
            if (strlen($passNueva) < 6) {
                echo json_encode(['ok' => false, 'msg' => 'La nueva contraseña debe tener al menos 6 caracteres.']);
                exit();
            }
            if ($passNueva !== $passConfirm) {
                echo json_encode(['ok' => false, 'msg' => 'Las contraseñas nuevas no coinciden.']);
                exit();
            }

            $admin = $this->adminModel->obtenerPorId($adminId);
            if (!$admin) {
                echo json_encode(['ok' => false, 'msg' => 'Administrador no encontrado.']);
                exit();
            }

            $passOk = ($passActual === $admin['contrasena']) || password_verify($passActual, $admin['contrasena']);
            if (!$passOk) {
                echo json_encode(['ok' => false, 'msg' => 'La contraseña actual es incorrecta.']);
                exit();
            }

            $hashNueva = password_hash($passNueva, PASSWORD_BCRYPT);
        }

        $actualizado = $this->adminModel->actualizarPerfil($adminId, $nombre, $correo, $hashNueva);

        if ($actualizado) {
            $_SESSION['admin_nombre'] = $nombre;
            $_SESSION['admin_correo'] = $correo;
            echo json_encode(['ok' => true, 'msg' => 'Perfil actualizado correctamente.', 'nombre' => $nombre]);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Error al actualizar. Intente nuevamente.']);
        }
        exit();
    }
    public function configuracion(): void      
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        if (!$adminId) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/login');
            exit();
        }

        $msg_perfil  = null; $tipo_perfil  = '';
        $msg_sistema = null; $tipo_sistema = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
            if ($_POST['accion'] === 'perfil') {
                $nombre  = trim($_POST['nombre_completo']   ?? '');
                $correo  = trim($_POST['correo_electronico'] ?? '');
                $passAct = $_POST['password_actual']  ?? '';
                $passNew = $_POST['password_nueva']   ?? '';
                $passCon = $_POST['password_confirmar'] ?? '';

                if (empty($nombre) || empty($correo)) {
                    $msg_perfil = 'Nombre y correo son obligatorios.';
                    $tipo_perfil = 'error';
                } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $msg_perfil = 'El correo electrónico no es válido.';
                    $tipo_perfil = 'error';
                } else {
                    $cambiarPass = false;
                    $errorPass   = false;
                    $hashNueva   = null;

                    if (!empty($passNew)) {
                        if (empty($passAct)) {
                            $msg_perfil  = 'Ingrese su contraseña actual para cambiarla.';
                            $tipo_perfil = 'error';
                            $errorPass   = true;
                        } elseif (strlen($passNew) < 6) {
                            $msg_perfil  = 'La nueva contraseña debe tener al menos 6 caracteres.';
                            $tipo_perfil = 'error';
                            $errorPass   = true;
                        } elseif ($passNew !== $passCon) {
                            $msg_perfil  = 'Las contraseñas nuevas no coinciden.';
                            $tipo_perfil = 'error';
                            $errorPass   = true;
                        } else {
                            $admin = $this->adminModel->obtenerPorId($adminId);
                            if (!$admin || (!password_verify($passAct, $admin['contrasena']) && $passAct !== $admin['contrasena'])) {
                                $msg_perfil  = 'La contraseña actual es incorrecta.';
                                $tipo_perfil = 'error';
                                $errorPass   = true;
                            } else {
                                $hashNueva = password_hash($passNew, PASSWORD_BCRYPT);
                                $cambiarPass = true;
                            }
                        }
                    }

                    if (!$errorPass) {
                        $actualizado = $this->adminModel->actualizarPerfil($adminId, $nombre, $correo, $hashNueva);
                        if ($actualizado) {
                            $_SESSION['admin_nombre'] = $nombre;
                            $_SESSION['admin_correo'] = $correo;
                            $msg_perfil  = 'Perfil actualizado correctamente.';
                            $tipo_perfil = 'exito';
                        } else {
                            $msg_perfil  = 'Error al actualizar el perfil.';
                            $tipo_perfil = 'error';
                        }
                    }
                }
            } elseif ($_POST['accion'] === 'sistema') {
                $campos_dias = ['dias_vencimiento_peticion','dias_vencimiento_queja',
                                'dias_vencimiento_reclamo','dias_vencimiento_sugerencia','dias_vencimiento_denuncia'];
                $valores = [];
                $err = false;

                foreach ($campos_dias as $c) {
                    $v = intval($_POST[$c] ?? 0);
                    if ($v < 1 || $v > 30) {
                        $msg_sistema  = "El valor para '$c' debe estar entre 1 y 30.";
                        $tipo_sistema = 'error';
                        $err = true;
                        break;
                    }
                    $valores[$c] = $v;
                }

                if (!$err) {
                    $correo_noti  = trim($_POST['correo_notificaciones'] ?? '');
                    $nombre_emp   = trim($_POST['nombre_empresa']        ?? '');

                    if (!empty($correo_noti) && !filter_var($correo_noti, FILTER_VALIDATE_EMAIL)) {
                        $msg_sistema  = 'El correo de notificaciones no es válido.';
                        $tipo_sistema = 'error';
                        $err = true;
                    }
                }

                if (!$err) {
                    $actualizado = $this->configuracionModel->actualizarConfiguracion(
                        $valores['dias_vencimiento_peticion'],
                        $valores['dias_vencimiento_queja'],
                        $valores['dias_vencimiento_reclamo'],
                        $valores['dias_vencimiento_sugerencia'],
                        $valores['dias_vencimiento_denuncia'],
                        $correo_noti,
                        $nombre_emp
                    );

                    if ($actualizado) {
                        $msg_sistema  = 'Configuración del sistema guardada correctamente.';
                        $tipo_sistema = 'exito';
                    } else {
                        $msg_sistema  = 'Error al guardar la configuración.';
                        $tipo_sistema = 'error';
                    }
                }
            }
        }

        $adminNombre = $_SESSION['admin_nombre'] ?? '';
        $adminCorreo = $_SESSION['admin_correo'] ?? '';
        $adminUsuario = $_SESSION['admin_usuario'] ?? '';

        $config = $this->configuracionModel->obtenerConfiguracion() ?? [];
        $config = array_merge([
            'dias_vencimiento_peticion'   => 15,
            'dias_vencimiento_queja'      => 15,
            'dias_vencimiento_reclamo'    => 15,
            'dias_vencimiento_sugerencia' => 15,
            'dias_vencimiento_denuncia'   => 15,
            'correo_notificaciones'       => '',
            'nombre_empresa'              => '',
        ], $config);

        $tabActiva = (isset($_POST['accion']) && $_POST['accion'] === 'sistema') ? 'sistema' : 'perfil';

        require_once __DIR__ . '/../views/admin/configuracion.php'; 
    }
    public function dashboard(): void          
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
        $adminRol = $_SESSION['admin_rol'] ?? 'admin';

        $stats = $this->pqrsModel->obtenerEstadisticasDashboard();
        $ultimasPQRS = $stats['ultimas'];

        require_once __DIR__ . '/../views/admin/dashboard_admin.php'; 
    }
    public function login(): void              
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/dashboard');
            exit();
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario  = trim($_POST['usuario'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($usuario) || empty($password)) {
                $error = 'Por favor ingrese usuario y contraseña.';
            } else {
                $admin = $this->adminModel->obtenerPorUsuario($usuario);

                if ($admin) {
                    $passwordValida = ($password === $admin['contrasena']) || password_verify($password, $admin['contrasena']);

                    if ($passwordValida) {
                        $_SESSION['admin_id']       = $admin['id'];
                        $_SESSION['admin_usuario']  = $admin['nombre_usuario'];
                        $_SESSION['admin_nombre']   = $admin['nombre_completo'];
                        $_SESSION['admin_correo']   = $admin['correo_electronico'];
                        $_SESSION['admin_rol']      = $admin['rol'];
                        $_SESSION['ultima_actividad'] = time();
                        $_SESSION['tiempo_inicio']    = time();

                        $this->adminModel->actualizarUltimoAcceso($admin['id']);

                        header('Location: ' . BASE_PATH . 'index.php?ruta=admin/dashboard');
                        exit();
                    } else {
                        $error = 'Contraseña incorrecta. Intente nuevamente.';
                    }
                } else {
                    $error = 'Usuario no encontrado o cuenta inactiva.';
                }
            }
        }

        require_once __DIR__ . '/../views/admin/login.php'; 
    }
    public function logout(): void             { require_once __DIR__ . '/../views/admin/logout.php'; }
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

        require_once __DIR__ . '/../views/admin/pqrs.php'; 
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

        require_once __DIR__ . '/../views/admin/pqrs_historial.php'; 
    }

    public function exportar_excel(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $tipo = $_GET['tipo'] ?? '';

        $resultado = $this->pqrsModel->obtenerParaExportacion($fechaInicio, $fechaFin, $tipo);
        
        $estados = $resultado['estados'];
        $totalRecibidas = array_sum($estados);
        $totalResueltas = $estados['RESUELTO'] ?? 0;
        $totalPendientes = ($estados['PENDIENTE'] ?? 0) + ($estados['EN_PROCESO'] ?? 0);
        $totalRechazadas = $estados['RECHAZADO'] ?? 0;
        $tiempoPromedio = $resultado['tiempo_promedio'];
        $enTiempo = $resultado['en_tiempo'];
        $porcentajeCumplimiento = $totalResueltas > 0 ? round(($enTiempo / $totalResueltas) * 100, 2) : 0;

        $tipoReporte = !empty($tipo) ? strtoupper($tipo) : 'GENERAL';
        $adminId = $_SESSION['admin_id'] ?? null;

        if ($adminId) {
            $this->pqrsModel->registrarReporte(
                $tipoReporte, $fechaInicio, $fechaFin,
                $totalRecibidas, $totalResueltas, $totalPendientes, $totalRechazadas,
                $tiempoPromedio, $porcentajeCumplimiento,
                'EXCEL', $adminId
            );
        }

        $data = $resultado['data'];
        $adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
        
        require_once __DIR__ . '/../views/admin/exportar_excel.php';
    }

    public function exportar_pdf(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $tipo = $_GET['tipo'] ?? '';

        $resultado = $this->pqrsModel->obtenerParaExportacion($fechaInicio, $fechaFin, $tipo);
        
        $estados = $resultado['estados'];
        $totalRecibidas = array_sum($estados);
        $totalResueltas = $estados['RESUELTO'] ?? 0;
        $totalPendientes = ($estados['PENDIENTE'] ?? 0) + ($estados['EN_PROCESO'] ?? 0);
        $totalRechazadas = $estados['RECHAZADO'] ?? 0;
        $tiempoPromedio = $resultado['tiempo_promedio'];
        $enTiempo = $resultado['en_tiempo'];
        $porcentajeCumplimiento = $totalResueltas > 0 ? round(($enTiempo / $totalResueltas) * 100, 2) : 0;

        $tipoReporte = !empty($tipo) ? strtoupper($tipo) : 'GENERAL';
        $adminId = $_SESSION['admin_id'] ?? null;

        if ($adminId) {
            $this->pqrsModel->registrarReporte(
                $tipoReporte, $fechaInicio, $fechaFin,
                $totalRecibidas, $totalResueltas, $totalPendientes, $totalRechazadas,
                $tiempoPromedio, $porcentajeCumplimiento,
                'PDF', $adminId
            );
        }

        $data = $resultado['data'];
        $adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
        
        require_once __DIR__ . '/../views/admin/exportar_pdf.php';
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

        require_once __DIR__ . '/../views/admin/alertas.php';
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
        
        // La vista usa la variable $pqrs (array) y las claves correspondientes
        require_once __DIR__ . '/../views/admin/pqrs_responder.php'; 
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

        require_once __DIR__ . '/../views/admin/pqrs_ver.php'; 
    }
    public function recuperar(): void          
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $mensaje = null;
        $tipo_mensaje = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = trim($_POST['correo'] ?? '');

            if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $mensaje = 'Ingrese un correo electrónico válido.';
                $tipo_mensaje = 'error';
            } else {
                $admin = $this->adminModel->obtenerPorCorreo($correo);

                if ($admin) {
                    $token = bin2hex(random_bytes(32));
                    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    $this->adminModel->actualizarTokenRecuperacion($admin['id'], $token, $expiracion);

                    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
                    $basePath = $isRailway ? '' : '/PROYECTO_PQRS';
                    $urlReset = "$protocolo://$host$basePath/index.php?ruta=admin/restablecer_contrasena&token=$token";

                    $emailService = new EmailService();
                    $enviado = $emailService->enviarCorreoRecuperacion($correo, $admin['nombre_completo'], $admin['nombre_usuario'], $urlReset);

                    if ($enviado) {
                        $mensaje = 'Se ha enviado un enlace de recuperación a su correo electrónico. Revise su bandeja de entrada.';
                        $tipo_mensaje = 'exito';
                    } else {
                        $mensaje = 'No se pudo enviar el correo. Contacte al administrador del sistema.';
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = 'Si el correo está registrado, recibirá un enlace de recuperación.';
                    $tipo_mensaje = 'exito';
                }
            }
        }

        require_once __DIR__ . '/../views/admin/recuperar_contrasena.php'; 
    }
    public function recuperar_contrasena(): void { $this->recuperar(); }
    public function reportes(): void           
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $filtro_fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $filtro_fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $filtro_tipo = $_GET['tipo'] ?? '';

        $metricas = $this->pqrsModel->obtenerReportesEstadisticas($filtro_fecha_inicio, $filtro_fecha_fin, $filtro_tipo);

        require_once __DIR__ . '/../views/admin/reportes.php'; 
    }
    public function restablecer_contrasena(): void 
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $mensaje = null;
        $tipo_mensaje = '';
        $tokenValido = false;
        $token = $_GET['token'] ?? $_POST['token'] ?? '';

        if (empty($token)) {
            $mensaje = 'Enlace de recuperación no válido.';
            $tipo_mensaje = 'error';
        } else {
            $admin = $this->adminModel->obtenerPorTokenRecuperacion($token);

            if ($admin) {
                $tokenValido = true;

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $passNueva = $_POST['password_nueva'] ?? '';
                    $passConfirm = $_POST['password_confirmar'] ?? '';

                    if (empty($passNueva) || strlen($passNueva) < 6) {
                        $mensaje = 'La contraseña debe tener al menos 6 caracteres.';
                        $tipo_mensaje = 'error';
                    } elseif ($passNueva !== $passConfirm) {
                        $mensaje = 'Las contraseñas no coinciden.';
                        $tipo_mensaje = 'error';
                    } else {
                        $hashNueva = password_hash($passNueva, PASSWORD_BCRYPT);
                        
                        $this->adminModel->actualizarContrasena($admin['id'], $hashNueva);
                        $this->adminModel->actualizarTokenRecuperacion($admin['id'], null, null);

                        $mensaje = 'Contraseña actualizada correctamente. Ahora puede iniciar sesión.';
                        $tipo_mensaje = 'exito';
                        $tokenValido = false;
                    }
                }
            } else {
                $mensaje = 'El enlace de recuperación ha expirado o no es válido. Solicite uno nuevo.';
                $tipo_mensaje = 'error';
            }
        }

        require_once __DIR__ . '/../views/admin/restablecer_contrasena.php'; 
    }

    // ─── Acción: Cambiar estado de una PQRS ──────────────────────────────────
    // Movida desde app/views/admin/pqrs_cambiar_estado.php (que era una vista con lógica)

    public function pqrs_cambiar_estado(): void
    {
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
