<?php
/* HU-RecuperarPass: Solicitud de recuperación de contraseña */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>public/css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <section class="login-section" aria-labelledby="recuperar-title">
        <div class="login-container">
            <div class="login-card">

                <div class="login-header">
                    <div class="login-icon">
                        <i class="bi bi-envelope-check"></i>
                    </div>
                    <h1 id="recuperar-title" class="login-title">Recuperar Contraseña</h1>
                    <p class="login-subtitle">Ingrese su correo electrónico registrado para recibir un enlace de recuperación</p>
                </div>

                <?php if ($mensaje): ?>
                <div class="login-<?php echo $tipo_mensaje === 'exito' ? 'exito' : 'error'; ?>" role="alert">
                    <i class="bi bi-<?php echo $tipo_mensaje === 'exito' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
                    <span><?php echo htmlspecialchars($mensaje); ?></span>
                </div>
                <?php endif; ?>

                <form action="<?php echo BASE_PATH; ?>index.php?ruta=admin/recuperar" method="POST" class="login-form">
                    <div class="login-grupo">
                        <label for="correo" class="login-label">
                            <i class="bi bi-envelope"></i>
                            Correo Electrónico
                        </label>
                        <input type="email" id="correo" name="correo" class="login-input"
                            placeholder="ejemplo@correo.com" autocomplete="email" autofocus required>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="bi bi-send"></i>
                        Enviar Enlace de Recuperación
                    </button>
                </form>

                <div class="login-volver">
                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/login">
                        <i class="bi bi-arrow-left"></i>
                        Volver al Login
                    </a>
                </div>

            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
