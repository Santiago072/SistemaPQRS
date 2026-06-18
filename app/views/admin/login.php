<?php
/* HU-Login: Autenticación de Administradores */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema PQRS</title>
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Hoja de estilos única del sistema -->  
     <link rel="stylesheet" href="<?php echo BASE_PATH; ?>public/css/estilos.css">
     <style>
        /* ── RECUPERAR CONTRASEÑA ────────────────────────────────────────── */
.login-recuperar {
    text-align: right;
    margin: -0.25rem 0 1rem;
}
.login-recuperar a {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    color: var(--color-gray-500);
    font-size: var(--font-size-sm);
    font-weight: 500;
    transition: color var(--transition-fast);
}
.login-recuperar a:hover {
    color: var(--color-primary);
}
.login-exito {
    margin: 0 var(--space-6) var(--space-4);
    padding: var(--space-3) var(--space-4);
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    gap: var(--space-3);
    color: #065f46;
    font-size: var(--font-size-sm);
}
.login-exito i {
    font-size: 1.25rem;
    flex-shrink: 0;
    color: var(--color-secondary);
}
.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    background: var(--color-gray-100);
    color: var(--color-gray-700);
    border: 1px solid var(--color-gray-300);
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: var(--font-size-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
}
.btn-secondary:hover {
    background: var(--color-gray-200);
}
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    background: var(--color-primary);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-weight: 600;
    font-size: var(--font-size-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
}
.btn-primary:hover {
    background: var(--color-primary-dark);
}
.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
    </style>
</head>

<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- ============================================
         SECCIÓN: LOGIN
         ============================================ -->
    <section class="login-section" aria-labelledby="login-title">
        <div class="login-container">

            <div class="login-card">

                <!-- Header del formulario -->
                <div class="login-header">
                    <div class="login-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h1 id="login-title" class="login-title">Panel Administrativo</h1>
                    <p class="login-subtitle">Ingrese sus credenciales para acceder al sistema</p>
                </div>

                <!-- Mensaje de error -->
                <?php if ($error): ?>
                <div class="login-error" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>
                        <?php echo htmlspecialchars($error); ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- Formulario -->
                <form id="formLogin" action="<?php echo BASE_PATH; ?>index.php?ruta=admin/login" method="POST" class="login-form"
                    onsubmit="return validarLogin()">

                    <div class="login-grupo">
                        <label for="usuario" class="login-label">
                            <i class="bi bi-person"></i>
                            Usuario
                        </label>
                        <input type="text" id="usuario" name="usuario" class="login-input"
                            placeholder="Ingrese su nombre de usuario" autocomplete="username" autofocus required>
                    </div>

                    <div class="login-grupo">
                        <label for="password" class="login-label">
                            <i class="bi bi-key"></i>
                            Contraseña
                        </label>
                        <div class="login-password-wrap">
                            <input type="password" id="password" name="password" class="login-input"
                                placeholder="Ingrese su contraseña" autocomplete="current-password" required>
                            <button type="button" class="login-toggle-password" onclick="togglePassword()"
                                aria-label="Mostrar contraseña">
                                <i class="bi bi-eye" id="icono-password"></i>
                            </button>
                        </div>
                    </div>

                    <!-- NUEVO: Enlace recuperar contraseña -->
                    <div class="login-recuperar">
                        <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/recuperar">
                            <i class="bi bi-question-circle"></i>
                            ¿Olvidó su contraseña?
                        </a>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Iniciar Sesión
                    </button>

                </form>

                <!-- Información de seguridad -->
                <div class="login-seguridad">
                    <i class="bi bi-shield-check"></i>
                    <p>Conexión segura. Su sesión expirará automáticamente después de <strong>30 minutos</strong> de
                        inactividad.</p>
                </div>

                <!-- Volver al inicio -->
                <div class="login-volver">
                    <a href="<?php echo BASE_PATH; ?>index.php">
                        <i class="bi bi-arrow-left"></i>
                        Volver al inicio
                    </a>
                </div>

            </div>

        </div>
    </section>

    <script>
        /**
         * Validación del formulario de login
         */
        function validarLogin() {
            const usuario = document.getElementById('usuario').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!usuario) {
                alert('Por favor ingrese su nombre de usuario.');
                document.getElementById('usuario').focus();
                return false;
            }

            if (!password) {
                alert('Por favor ingrese su contraseña.');
                document.getElementById('password').focus();
                return false;
            }

            if (password.length < 4) {
                alert('La contraseña debe tener al menos 4 caracteres.');
                document.getElementById('password').focus();
                return false;
            }

            return true;
        }

        /**
         * Mostrar/ocultar contraseña
         */
        function togglePassword() {
            const input = document.getElementById('password');
            const icono = document.getElementById('icono-password');

            if (input.type === 'password') {
                input.type = 'text';
                icono.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icono.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>

</html>
