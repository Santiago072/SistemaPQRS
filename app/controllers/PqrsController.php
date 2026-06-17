<?php
class PqrsController {
    public function formulario() {
        $tipoPQRS = isset($_GET['tipo_pqrs']) ? htmlspecialchars($_GET['tipo_pqrs']) : 'peticion';
        $nombresTipos = [
            'peticion'  => 'Petición',
            'queja'     => 'Queja',
            'reclamo'   => 'Reclamo',
            'sugerencia'=> 'Sugerencia',
            'denuncia'  => 'Denuncia'
        ];
        $nombreTipo = $nombresTipos[$tipoPQRS] ?? 'Petición';
        require_once __DIR__ . '/../views/pqrs/formulario.php';
    }

    public function radicar() {
        // La logica copiada se ejecuta aqui
        // Reemplazar header('Location: confirmacion.php?id=$pqrs_id') por header('Location: '.BASE_PATH.'index.php?ruta=pqrs/confirmacion&id=$pqrs_id')
        
/**
 * HU-04 + HU-05: Formulario Adaptable + Generación de Código + Envío de Correo
 * Usa PHPMailer con SMTP (contraseña de aplicación)
 */

// ⚡ SESSION START PRIMERO — antes de cualquier output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ─── HELPER: Log seguro ───────────────────────────────────────────────────────
function logEmail(string $mensaje): void {
    $paths = [
        __DIR__ . '/../../logs/email_log.txt',
        '/tmp/pqrs_email_log.txt',
    ];
    foreach ($paths as $path) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (@file_put_contents($path, $mensaje, FILE_APPEND | LOCK_EX) !== false) {
            break;
        }
    }
}

/**
 * Envía el correo de confirmación usando PHPMailer SMTP
 */
function enviarCorreoPQRS(
    string $para,
    string $nombre,
    string $codigo_radicado,
    string $tipo_pqrs,
    string $asunto_solicitud,
    string $fecha_vencimiento,
    string $host
): bool {

    $cfg = require __DIR__ . '/../../config/email_config.php';

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
        $mail->addAddress($para, $nombre ?: 'Usuario');
        $mail->Subject = "Confirmacion de Radicacion PQRS - $codigo_radicado";

        $mail->isHTML(true);
        $mail->Body = "
    }

    public function confirmacion() {
        $id = $_GET['id'] ?? '';
        require_once __DIR__ . '/../views/pqrs/confirmacion.php';
    }
}
