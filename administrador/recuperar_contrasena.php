<?php
/**
 * HU-RecuperarPass: Solicitud de recuperación de contraseña
 * Genera token, lo guarda en BD y envía correo con enlace de restablecimiento
 */

session_start();
require_once '../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mensaje = null;
$tipo_mensaje = ''; // 'exito' o 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');

    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'Ingrese un correo electrónico válido.';
        $tipo_mensaje = 'error';
    } else {
        $con = conexion();
        if (!$con) {
            $mensaje = 'Error de conexión con la base de datos.';
            $tipo_mensaje = 'error';
        } else {
            // Buscar administrador activo con ese correo
            $stmt = mysqli_prepare($con, "SELECT id, nombre_completo, nombre_usuario FROM administrador WHERE correo_electronico = ? AND activo = 'activo' LIMIT 1");
            mysqli_stmt_bind_param($stmt, 's', $correo);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {
                $admin = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);

                // Generar token único
                $token = bin2hex(random_bytes(32));
                $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Guardar token en BD
                $stmtToken = mysqli_prepare($con, "UPDATE administrador SET token_recuperacion = ?, token_expiracion = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmtToken, 'ssi', $token, $expiracion, $admin['id']);
                mysqli_stmt_execute($stmtToken);
                mysqli_stmt_close($stmtToken);

                // Construir URL de restablecimiento
                $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
                $basePath = $isRailway ? '' : '/PROYECTO_PQRS';
                $urlReset = "$protocolo://$host$basePath/administrador/restablecer_contrasena.php?token=$token";

                // Enviar correo
                $enviado = enviarCorreoRecuperacion($correo, $admin['nombre_completo'], $admin['nombre_usuario'], $urlReset);

                if ($enviado) {
                    $mensaje = 'Se ha enviado un enlace de recuperación a su correo electrónico. Revise su bandeja de entrada.';
                    $tipo_mensaje = 'exito';
                } else {
                    $mensaje = 'No se pudo enviar el correo. Contacte al administrador del sistema.';
                    $tipo_mensaje = 'error';
                }
            } else {
                // Por seguridad, mostrar el mismo mensaje aunque no exista
                mysqli_stmt_close($stmt);
                $mensaje = 'Si el correo está registrado, recibirá un enlace de recuperación.';
                $tipo_mensaje = 'exito';
            }
            mysqli_close($con);
        }
    }
}

/**
 * Envía correo de recuperación usando PHPMailer
 */
function enviarCorreoRecuperacion($para, $nombre, $usuario, $urlReset) {
    $cfg = require __DIR__ . '/../config/email_config.php';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $cfg['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['smtp_user'];
        $mail->Password   = $cfg['smtp_password'];
        $mail->SMTPSecure = $cfg['smtp_encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $cfg['smtp_port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($cfg['from_email'], $cfg['from_name']);
        $mail->addAddress($para, $nombre ?: 'Administrador');
        $mail->Subject = 'Recuperación de Contraseña - Sistema PQRS';

        $mail->isHTML(true);
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family:Arial,sans-serif;line-height:1.6;color:#333;margin:0;padding:0'>
            <div style='max-width:600px;margin:0 auto;padding:20px'>
                <div style='background:linear-gradient(135deg,#1e40af,#1e3a8a);color:#fff;padding:30px;text-align:center;border-radius:10px 10px 0 0'>
                    <h1 style='margin:0;font-size:22px'>Recuperación de Contraseña</h1>
                    <p style='margin:10px 0 0;opacity:.8'>Sistema PQRS</p>
                </div>
                <div style='background:#f9fafb;padding:30px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 10px 10px'>
                    <p>Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</p>
                    <p>Recibimos una solicitud para restablecer la contraseña de su cuenta <strong>" . htmlspecialchars($usuario) . "</strong>.</p>
                    <p>Haga clic en el siguiente botón para crear una nueva contraseña:</p>
                    <div style='text-align:center;margin:25px 0'>
                        <a href='" . htmlspecialchars($urlReset) . "' style='background:#1e40af;color:#fff;padding:14px 30px;text-decoration:none;border-radius:8px;font-weight:600;display:inline-block'>Restablecer Contraseña</a>
                    </div>
                    <p style='font-size:13px;color:#6b7280'>Este enlace expirará en <strong>1 hora</strong>. Si no solicitó este cambio, ignore este correo.</p>
                    <hr style='border:none;border-top:1px solid #e5e7eb;margin:20px 0'>
                    <p style='font-size:12px;color:#9ca3af;text-align:center'>Sistema PQRS - Correo automático, no responder.</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando correo recuperación: " . $e->getMessage());
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

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

                <form action="recuperar_contrasena.php" method="POST" class="login-form">
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
                    <a href="login.php">
                        <i class="bi bi-arrow-left"></i>
                        Volver al Login
                    </a>
                </div>

            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
</body>
</html>