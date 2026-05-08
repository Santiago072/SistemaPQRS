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

// Redirigir al login
header('Location: login.php?logout=1');
exit();