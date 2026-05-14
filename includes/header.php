<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header - Sistema PQRS</title>
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Hoja de estilos única del sistema -->
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <header class="header">
        <div class="container header-container">
            <!-- Logo -->
            <a href="../index.php" class="logo" aria-label="Inicio - Sistema PQRS">
                <span class="logo-icon" aria-hidden="true">
                    <i class="bi bi-clipboard-data"></i>
                </span>
                <span>Sistema PQRS</span>
            </a>

            <!-- Login Admin (discreto, esquina superior derecha) -->
            <nav class="nav-admin" aria-label="Navegación administrativa">
                <a href="administrador/login.php" class="btn btn-outline" aria-label="Acceder al panel de administración">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Administrador</span>
                </a>
            </nav>
        </div>
    </header>
</body>
</html>