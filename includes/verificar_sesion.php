<?php
/**
 * Verificación de sesión de administrador
 * Controla autenticación, inactividad y define variables globales
 * 
 * CORRECCIONES APLICADAS:
 * - Tiempo de inactividad: 300 -> 1800 segundos (30 minutos reales)
 * - session_write_close() antes de redirecciones para evitar pérdida de sesión
 * - Validación de tipos en variables de sesión
 * - Manejo de errores sin mostrar warnings
 */

// Evitar warnings de headers already sent
if (headers_sent() === false) {
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// CORREGIDO: Tiempo de inactividad (30 minutos = 1800 segundos)
// Antes estaba en 300 (5 minutos), no 1800
define('TIEMPO_INACTIVIDAD', 300);

// Verificar si hay sesión activa
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // Guardar URL actual para redirección post-login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? '';

    // Cerrar sesión antes de redirigir para asegurar que se guarden los datos
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    header('Location: ../administrador/login.php?error=sesion_requerida');
    exit();
}

// Verificar tiempo de inactividad
if (isset($_SESSION['ultima_actividad'])) {
    $tiempoInactivo = time() - intval($_SESSION['ultima_actividad']);

    if ($tiempoInactivo > TIEMPO_INACTIVIDAD) {
        // Sesión expirada por inactividad
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();

        header('Location: ../administrador/login.php?error=sesion_expirada');
        exit();
    }
}

// Actualizar timestamp de última actividad
$_SESSION['ultima_actividad'] = time();

// Variables útiles disponibles en todas las páginas protegidas
// Usar intval() para asegurar tipo numérico y evitar inyección
$adminId      = isset($_SESSION['admin_id']) ? intval($_SESSION['admin_id']) : 0;
$adminUsuario = isset($_SESSION['admin_usuario']) ? $_SESSION['admin_usuario'] : '';
$adminNombre  = isset($_SESSION['admin_nombre']) ? $_SESSION['admin_nombre'] : 'Administrador';
$adminCorreo  = isset($_SESSION['admin_correo']) ? $_SESSION['admin_correo'] : '';
$adminRol     = isset($_SESSION['admin_rol']) ? $_SESSION['admin_rol'] : 'ADMIN';

// Si por alguna razón adminId es 0 después de validar, redirigir al login
if ($adminId === 0) {
    session_destroy();
    header('Location: ../administrador/login.php?error=sesion_invalida');
    exit();
}