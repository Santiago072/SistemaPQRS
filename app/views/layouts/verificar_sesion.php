<?php
/**
 * Verificación de sesión de administrador
 * Controla autenticación, inactividad y define variables globales
 */

// Evitar warnings de headers already sent
if (headers_sent() === false) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// 30 minutos
define('TIEMPO_INACTIVIDAD', 1800);

// Verificar si hay sesión activa
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? '';

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    header('Location: ' . BASE_PATH . 'index.php?ruta=admin/login&error=sesion_requerida');
    exit();
}

// Verificar tiempo de inactividad
if (isset($_SESSION['ultima_actividad'])) {
    $tiempoInactivo = time() - intval($_SESSION['ultima_actividad']);

    if ($tiempoInactivo > TIEMPO_INACTIVIDAD) {
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();

        header('Location: ' . BASE_PATH . 'index.php?ruta=admin/login&error=sesion_expirada');
        exit();
    }
}

// Actualizar timestamp de última actividad
$_SESSION['ultima_actividad'] = time();

// Variables útiles
$adminId      = isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : 0;
$adminUsuario = isset($_SESSION['admin_usuario']) ? $_SESSION['admin_usuario'] : '';
$adminNombre  = isset($_SESSION['admin_nombre']) ? $_SESSION['admin_nombre'] : 'Administrador';
$adminCorreo  = isset($_SESSION['admin_correo']) ? $_SESSION['admin_correo'] : '';
$adminRol     = isset($_SESSION['admin_rol']) ? $_SESSION['admin_rol'] : 'ADMIN';

if ($adminId === 0) {
    session_destroy();
    header('Location: ' . BASE_PATH . 'index.php?ruta=admin/login&error=sesion_invalida');
    exit();
}
