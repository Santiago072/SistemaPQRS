<?php
namespace App\Controllers\Admin;

use App\Models\AdminModel;
use App\Services\EmailService;

class AuthController
{
    private AdminModel $adminModel;

    public function __construct(AdminModel $adminModel)
    {
        $this->adminModel = $adminModel;
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

        require_once __DIR__ . '/../../../app/views/admin/login.php'; 
    }

    public function logout(): void             
    { 
        require_once __DIR__ . '/../../../app/views/admin/logout.php'; 
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
                    $basePath = $isRailway ? '' : '/SistemaPQRS';
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

        require_once __DIR__ . '/../../../app/views/admin/recuperar_contrasena.php'; 
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

        require_once __DIR__ . '/../../../app/views/admin/restablecer_contrasena.php'; 
    }
}
