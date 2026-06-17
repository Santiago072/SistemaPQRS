<?php
/**
 * HU-RecuperarPass: Restablecer contraseña con token
 * Valida token, muestra formulario y actualiza contraseña
 */

session_start();
require_once __DIR__ . '/../../../config/conexion.php';

$mensaje = null;
$tipo_mensaje = '';
$tokenValido = false;
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (empty($token)) {
    $mensaje = 'Enlace de recuperación no válido.';
    $tipo_mensaje = 'error';
} else {
    $con = conexion();
    if ($con) {
        // Verificar token válido y no expirado
        $stmt = mysqli_prepare($con, "SELECT id, nombre_usuario FROM administrador WHERE token_recuperacion = ? AND token_expiracion > NOW() AND estado = 'activo' LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);
            $tokenValido = true;

            // Procesar cambio de contraseña
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $passNueva = $_POST['password_nueva'] ?? '';
                $passConfirm = $_POST['password_confirmar'] ?? '';

                if (empty($passNueva) || strlen($passNueva) < 6) {
                    $mensaje = 'La contraseña debe tener al menos 6 caracteres.';
                    $tipo_mensaje = 'error';
                } elseif ($passNueva !== $passConfirm) {
                    $mensaje = 'Las contraseñas no coinciden.';
                    $tipo_mensaje = 'error';
                } else {
                    $hashNueva = password_hash($passNueva, PASSWORD_BCRYPT);
                    $stmtUpd = mysqli_prepare($con, "UPDATE administrador SET contrasena = ?, token_recuperacion = NULL, token_expiracion = NULL WHERE id = ?");
                    mysqli_stmt_bind_param($stmtUpd, 'si', $hashNueva, $admin['id']);

                    if (mysqli_stmt_execute($stmtUpd)) {
                        $mensaje = 'Contraseña actualizada correctamente. Ahora puede iniciar sesión.';
                        $tipo_mensaje = 'exito';
                        $tokenValido = false; // Ocultar formulario
                    } else {
                        $mensaje = 'Error al actualizar. Intente nuevamente.';
                        $tipo_mensaje = 'error';
                    }
                    mysqli_stmt_close($stmtUpd);
                }
            }
        } else {
            $mensaje = 'El enlace de recuperación ha expirado o no es válido. Solicite uno nuevo.';
            $tipo_mensaje = 'error';
        }
        mysqli_stmt_close($stmt);
        mysqli_close($con);
    } else {
        $mensaje = 'Error de conexión con la base de datos.';
        $tipo_mensaje = 'error';
    }
}
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
