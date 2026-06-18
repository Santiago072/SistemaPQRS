<?php
/* HU-RecuperarPass: Restablecer contraseña con token */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>public/css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <section class="login-section" aria-labelledby="restablecer-title">
        <div class="login-container">
            <div class="login-card">

                <div class="login-header">
                    <div class="login-icon">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <h1 id="restablecer-title" class="login-title">Nueva Contraseña</h1>
                    <p class="login-subtitle">Ingrese su nueva contraseña para restablecer el acceso</p>
                </div>

                <?php if ($mensaje): ?>
                <div class="login-<?php echo $tipo_mensaje === 'exito' ? 'exito' : 'error'; ?>" role="alert">
                    <i class="bi bi-<?php echo $tipo_mensaje === 'exito' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?>"></i>
                    <span>
                        <?php echo htmlspecialchars($mensaje); ?>
                        <?php if ($tipo_mensaje === 'exito'): ?>
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/login" style="display:inline-flex;align-items:center;gap:0.25rem;margin-top:0.5rem;color:#065f46;font-weight:600;text-decoration:underline;">
                                <i class="bi bi-box-arrow-in-right"></i> Ir al Login
                            </a>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>

                <?php if ($tokenValido): ?>
                <form action="restablecer_contrasena.php" method="POST" class="login-form" onsubmit="return validarReset()">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="login-grupo">
                        <label for="password_nueva" class="login-label">
                            <i class="bi bi-lock"></i>
                            Nueva Contraseña
                        </label>
                        <div class="login-password-wrap">
                            <input type="password" id="password_nueva" name="password_nueva" class="login-input"
                                placeholder="Mínimo 6 caracteres" autocomplete="new-password" required minlength="6">
                            <button type="button" class="login-toggle-password" onclick="togglePass('password_nueva','ico1')" aria-label="Mostrar">
                                <i class="bi bi-eye" id="ico1"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-grupo">
                        <label for="password_confirmar" class="login-label">
                            <i class="bi bi-lock-fill"></i>
                            Confirmar Contraseña
                        </label>
                        <div class="login-password-wrap">
                            <input type="password" id="password_confirmar" name="password_confirmar" class="login-input"
                                placeholder="Repita la contraseña" autocomplete="new-password" required minlength="6">
                            <button type="button" class="login-toggle-password" onclick="togglePass('password_confirmar','ico2')" aria-label="Mostrar">
                                <i class="bi bi-eye" id="ico2"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="bi bi-check-lg"></i>
                        Restablecer Contraseña
                    </button>
                </form>
                <?php endif; ?>

                <div class="login-volver">
                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/login">
                        <i class="bi bi-arrow-left"></i>
                        Volver al Login
                    </a>
                </div>

            </div>
        </div>
    </section>

    <script>
        function validarReset() {
            var p1 = document.getElementById('password_nueva').value;
            var p2 = document.getElementById('password_confirmar').value;
            if (p1.length < 6) { alert('La contraseña debe tener al menos 6 caracteres.'); return false; }
            if (p1 !== p2) { alert('Las contraseñas no coinciden.'); return false; }
            return true;
        }
        function togglePass(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (input.type === 'password') { input.type = 'text'; icon.classList.replace('bi-eye','bi-eye-slash'); }
            else { input.type = 'password'; icon.classList.replace('bi-eye-slash','bi-eye'); }
        }
    </script>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
