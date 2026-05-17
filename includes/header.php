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
                <a href="<?php echo $baseUrl; ?>administrador/login.php" class="btn btn-outline">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Administrador</span>
                </a>
            </nav>
        </div>
    </header>