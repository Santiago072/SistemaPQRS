<?php
/**
 * Cerrar sesión de administrador
 * Destruye la sesión y redirige al login
 */

session_start();

// Limpiar todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destruir la sesión
session_destroy();

// Detectar entorno para redirección correcta
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
$baseUrl = $isRailway ? '/' : '/PROYECTO_PQRS/';

// Redirigir al login
header('Location: ' . $baseUrl . 'administrador/login.php?logout=1');
exit();
