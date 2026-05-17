<?php
// Detectar si estamos en Railway o XAMPP local
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);

// Base URL: en Railway es raíz, en XAMPP es /PROYECTO_PQRS/
if ($isRailway) {
    $baseUrl = '/';
} else {
    $baseUrl = '/PROYECTO_PQRS/';
}

// Verificar si hay sesión de administrador activa
// session_start() SOLO si no hay sesión activa (evita el notice)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sesionActiva = isset($_SESSION['admin_id']) || isset($_SESSION['usuario_id']) || isset($_SESSION['rol']);
?>
<header class="header">
    <div class="container header-container">
        <a href="<?php echo $baseUrl; ?>index.php" class="logo" aria-label="Inicio - Sistema PQRS">
            <span class="logo-icon" aria-hidden="true">
                <i class="bi bi-clipboard-data"></i>
            </span>
            <span>Sistema PQRS</span>
        </a>

        <nav class="nav-admin" aria-label="Navegación administrativa">
            <?php if ($sesionActiva): ?>
                <!-- Si hay sesión activa: mostrar SOLO botón de cerrar sesión -->
                <a href="<?php echo $baseUrl; ?>administrador/logout.php" class="btn btn-outline btn-cerrar-sesion">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    <span>Cerrar Sesión</span>
                </a>
            <?php else: ?>
                <!-- Si NO hay sesión: mostrar botón de administrador -->
                <a href="<?php echo $baseUrl; ?>administrador/login.php" class="btn btn-outline">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Administrador</span>
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>