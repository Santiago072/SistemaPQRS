<?php
/**
 * HU-Login: Autenticación de Administradores
 * Formulario de login con validación, sesión activa y expiración por inactividad
 */

require_once '../config/conexion.php';

// Si ya hay sesión activa, redirigir al dashboard
session_start();
if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    header('Location: dashboard_admin.php');
    exit();
}

$error = null;

// Procesar POST de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        $error = 'Por favor ingrese usuario y contraseña.';
    } else {
        $con = conexion();
        if (!$con) {
            $error = 'Error de conexión con la base de datos.';
        } else {
            // Buscar administrador (usando prepared statement sería ideal, manteniendo compatibilidad)
            $usuario_safe = mysqli_real_escape_string($con, $usuario);
            $sql = "SELECT id, nombre_usuario, contrasena, nombre_completo, correo_electronico, rol, activo 
                    FROM administrador 
                    WHERE nombre_usuario = '$usuario_safe' AND activo = 'activo' 
                    LIMIT 1";
            $result = mysqli_query($con, $sql);

            if ($result && mysqli_num_rows($result) === 1) {
                $admin = mysqli_fetch_assoc($result);

                // Verificar contraseña (hash o texto plano según implementación)
                // En producción usar password_verify() con hash bcrypt
                $passwordValida = ($password === $admin['contrasena']) || 
                                  password_verify($password, $admin['contrasena']);

                if ($passwordValida) {
                    // Crear sesión
                    $_SESSION['admin_id']       = $admin['id'];
                    $_SESSION['admin_usuario']  = $admin['nombre_usuario'];
                    $_SESSION['admin_nombre']   = $admin['nombre_completo'];
                    $_SESSION['admin_correo']   = $admin['correo_electronico'];
                    $_SESSION['admin_rol']      = $admin['rol'];
                    $_SESSION['ultima_actividad'] = time();
                    $_SESSION['tiempo_inicio']    = time();

                    // Actualizar último acceso
                    $adminId = $admin['id'];
                    mysqli_query($con, "UPDATE administrador SET ultimo_acceso = NOW() WHERE id = $adminId");

                    mysqli_close($con);

                    // Redirigir al dashboard
                    header('Location: dashboard_admin.php');
                    exit();
                } else {
                    $error = 'Contraseña incorrecta. Intente nuevamente.';
                }
            } else {
                $error = 'Usuario no encontrado o cuenta inactiva.';
            }
            mysqli_close($con);
        }
    }
}
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
     <link rel="stylesheet" href="../css/estilos.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

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
                <form id="formLogin" action="login.php" method="POST" class="login-form"
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

                    <div class="login-opciones">
                        <label class="login-recordar">
                            <input type="checkbox" name="recordar" value="1">
                            <span>Recordar sesión en este dispositivo</span>
                        </label>
                        <a href="#" class="login-olvidar"
                            onclick="alert('Contacte al administrador del sistema para restablecer su contraseña.'); return false;">
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
                    <p>Conexión segura. Su sesión expirará automáticamente después de <strong>5 minutos</strong> de
                        inactividad.</p>
                </div>

                <!-- Volver al inicio -->
                <div class="login-volver">
                    <a href="../index.php">
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

    <?php include '../includes/footer.php'; ?>
</body>

</html>