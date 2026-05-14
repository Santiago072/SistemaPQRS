<?php
// config/config.php - Configuración central del sistema

if (defined('BASE_URL')) return;

$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detectar Railway
$isRailway = (getenv('RAILWAY_ENVIRONMENT') !== false) 
          || (strpos($host, 'railway.app') !== false);

if ($isRailway) {
    define('BASE_URL', $protocolo . '://' . $host . '/');
} else {
    define('BASE_URL', $protocolo . '://' . $host . '/PROYECTO_PQRS/');
}

define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);