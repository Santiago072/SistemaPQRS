<?php
/**
 * Header del sistema PQRS
 * Detecta sesión activa sin causar errores de headers
 */

// El BASE_PATH ya debería estar definido por index.php
if (!defined('BASE_PATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
    define('BASE_PATH', $isRailway ? '/' : '/PROYECTO_PQRS/');
}

// Verificar sesión SIN iniciarla aquí
// Si la sesión no está activa, simplemente no hay sesión ($sesionActiva = false)
// Esto evita el error "headers already sent" en páginas públicas
$sesionActiva = (session_status() === PHP_SESSION_ACTIVE) && 
                (isset($_SESSION['admin_id']) || isset($_SESSION['usuario_id']) || isset($_SESSION['rol']));
?>
<header class="header">
    <div class="container header-container">
        <a href="<?php echo BASE_PATH; ?>index.php" class="logo" aria-label="Inicio - Sistema PQRS">
            <span class="logo-icon" aria-hidden="true">
                <i class="bi bi-clipboard-data"></i>
            </span>
            <span>Sistema PQRS</span>
        </a>

        <nav class="nav-admin" aria-label="Navegación administrativa">
            <?php if ($sesionActiva): ?>
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/logout" class="btn btn-outline btn-cerrar-sesion">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    <span>Cerrar Sesión</span>
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/login" class="btn btn-outline">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Administrador</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>