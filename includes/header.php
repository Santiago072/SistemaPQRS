<?php
if (!isset($basePath)) {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $depth = substr_count($scriptDir, '/') - 1;
    $basePath = str_repeat('../', max(0, $depth));
    if (empty($basePath)) $basePath = './';
}
?>
    <header class="header">
        <div class="container header-container">
            <a href="<?php echo $basePath; ?>index.php" class="logo">
                <span class="logo-icon"><i class="bi bi-clipboard-data"></i></span>
                <span>Sistema PQRS</span>
            </a>
            <nav class="nav-admin">
                <a href="<?php echo $basePath; ?>administrador/login.php" class="btn btn-outline">
                    <i class="bi bi-shield-lock"></i>
                    <span>Administrador</span>
                </a>
            </nav>
        </div>
    </header>