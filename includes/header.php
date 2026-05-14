<?php
// Definir BASE_URL si no existe (compatible con cualquier inclusión)
if (!defined('BASE_URL')) {
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Detectar Railway
    $isRailway = (getenv('RAILWAY_ENVIRONMENT') !== false) 
              || (strpos($host, 'railway.app') !== false);
    
    if ($isRailway) {
        define('BASE_URL', $protocolo . '://' . $host . '/');
    } else {
        // XAMPP local
        define('BASE_URL', $protocolo . '://' . $host . '/PROYECTO_PQRS/');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/estilos.css">
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <a href="<?php echo BASE_URL; ?>index.php" class="logo" aria-label="Inicio - Sistema PQRS">
                <span class="logo-icon" aria-hidden="true">
                    <i class="bi bi-clipboard-data"></i>
                </span>
                <span>Sistema PQRS</span>
            </a>

            <nav class="nav-admin" aria-label="Navegación administrativa">
                <a href="<?php echo BASE_URL; ?>administrador/login.php" class="btn btn-outline">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Administrador</span>
                </a>
            </nav>
        </div>
    </header>