<?php
namespace App\Controllers\Admin;

use App\Models\AdminModel;
use App\Models\ConfiguracionModel;

class ConfigController
{
    private AdminModel $adminModel;
    private ConfiguracionModel $configuracionModel;

    public function __construct(AdminModel $adminModel, ConfiguracionModel $configuracionModel)
    {
        $this->adminModel = $adminModel;
        $this->configuracionModel = $configuracionModel;
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

        require_once __DIR__ . '/../../../app/views/admin/configuracion.php'; 
    }

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
}
