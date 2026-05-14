<?php
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}
?>
    <header class="header">
        <div class="container header-container">
            <a href="<?php echo BASE_URL; ?>index.php" class="logo">
                <span class="logo-icon"><i class="bi bi-clipboard-data"></i></span>
                <span>Sistema PQRS</span>
            </a>
            <nav class="nav-admin">
                <a href="<?php echo BASE_URL; ?>administrador/login.php" class="btn btn-outline">
                    <i class="bi bi-shield-lock"></i>
                    <span>Administrador</span>
                </a>
            </nav>
        </div>
    </header>