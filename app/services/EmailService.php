<?php
/**
 * EmailService.php — Servicio centralizado de envío de correos
 *
 * Principio: SRP - esta clase tiene UNA sola responsabilidad: enviar correos.
 * Principio: DIP - los controladores dependen de esta abstracción, no de PHPMailer directamente.
 *
 * Elimina la duplicación de lógica SMTP que existía en PqrsController y pqrs_responder.php.
 */

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private array $cfg;

    public function __construct()
    {
        $cfgPath = dirname(__DIR__, 2) . '/config/email_config.php';
        
        if (file_exists($cfgPath)) {
            $this->cfg = require $cfgPath;
        } else {
            // Fallback a variables de entorno (Docker / VPS)
            $this->cfg = [
                'smtp_host'       => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
                'smtp_port'       => getenv('SMTP_PORT') ?: 587,
                'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
                'smtp_user'       => getenv('SMTP_USER') ?: '',
                'smtp_password'   => getenv('SMTP_PASSWORD') ?: '',
                'from_email'      => getenv('FROM_EMAIL') ?: '',
                'from_name'       => getenv('FROM_NAME') ?: 'Sistema PQRS',
            ];
            
            if (empty($this->cfg['smtp_user']) || empty($this->cfg['smtp_password'])) {
                throw new \RuntimeException('Configuración SMTP no encontrada. Crea config/email_config.php o configura las variables de entorno.');
            }
        }
    }

    // ─── Método privado de log ────────────────────────────────────────────────

    private function log(string $mensaje): void
    {
        $paths = [
            dirname(__DIR__, 2) . '/logs/email_log.txt',
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

    // ─── Constructor base de PHPMailer ────────────────────────────────────────

    private function crearMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $this->cfg['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->cfg['smtp_user'];
        $mail->Password   = $this->cfg['smtp_password'];
        $mail->SMTPSecure = $this->cfg['smtp_encryption'] === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port     = $this->cfg['smtp_port'];
        $mail->CharSet  = 'UTF-8';
        $mail->setFrom($this->cfg['from_email'], $this->cfg['from_name']);
        return $mail;
    }

    // ─── Correo de confirmación al radicar nueva PQRS ────────────────────────

    public function enviarConfirmacionRadicacion(
        string $para,
        string $nombre,
        string $codigoRadicado,
        string $tipoPqrs,
        string $asunto,
        string $fechaVencimiento,
        string $host
    ): bool {
        $tipoLabel = [
            'peticion'   => 'Peticion',
            'queja'      => 'Queja',
            'reclamo'    => 'Reclamo',
            'sugerencia' => 'Sugerencia',
            'denuncia'   => 'Denuncia',
        ][$tipoPqrs] ?? ucfirst($tipoPqrs);

        try {
            $mail = $this->crearMailer();
            $mail->addAddress($para, $nombre ?: 'Ciudadano');
            $mail->Subject = "Confirmacion de Radicacion PQRS - {$codigoRadicado}";
            $mail->isHTML(true);
            $mail->Body = $this->plantillaConfirmacion(
                $nombre, $codigoRadicado, $tipoLabel, $asunto, $fechaVencimiento, $host
            );
            $mail->AltBody = "Su solicitud {$codigoRadicado} fue radicada. Fecha limite: {$fechaVencimiento}";
            $mail->send();
            $this->log(date('Y-m-d H:i:s') . " | ENVIADO | Para: {$para} | Codigo: {$codigoRadicado}\n");
            return true;
        } catch (Exception $e) {
            $this->log(date('Y-m-d H:i:s') . " | FALLO | Para: {$para} | Codigo: {$codigoRadicado} | Error: {$e->getMessage()}\n");
            return false;
        }
    }

    // ─── Correo de respuesta del administrador ────────────────────────────────

    public function enviarRespuestaAdministrador(
        string $para,
        string $nombre,
        string $codigoRadicado,
        string $tipoPqrs,
        string $asunto,
        string $contenidoRespuesta,
        string $nuevoEstado,
        string $host
    ): bool {
        $tipoLabel = [
            'peticion'   => 'Peticion',
            'queja'      => 'Queja',
            'reclamo'    => 'Reclamo',
            'sugerencia' => 'Sugerencia',
            'denuncia'   => 'Denuncia',
        ][$tipoPqrs] ?? ucfirst($tipoPqrs);

        $estadoInfo = [
            'RESUELTO'   => ['texto' => 'Resuelto',   'color' => '#059669', 'icon' => '&#10003;'],
            'RECHAZADO'  => ['texto' => 'Rechazado',  'color' => '#dc2626', 'icon' => '&#10007;'],
            'EN_PROCESO' => ['texto' => 'En Proceso', 'color' => '#1e40af', 'icon' => '&#8635;'],
            'PENDIENTE'  => ['texto' => 'Pendiente',  'color' => '#d97706', 'icon' => '&#9711;'],
        ];
        $estado = $estadoInfo[$nuevoEstado] ?? $estadoInfo['PENDIENTE'];

        try {
            $mail = $this->crearMailer();
            $mail->addAddress($para, $nombre ?: 'Ciudadano');
            $mail->Subject = "Respuesta a su solicitud PQRS - {$codigoRadicado}";
            $mail->isHTML(true);
            $mail->Body = $this->plantillaRespuesta(
                $nombre, $codigoRadicado, $tipoLabel, $asunto,
                $contenidoRespuesta, $estado, $host
            );
            $mail->AltBody = "Su solicitud {$codigoRadicado} ha sido atendida. Estado: {$estado['texto']}";
            $mail->send();
            $this->log(date('Y-m-d H:i:s') . " | ENVIADO-RESPUESTA | Para: {$para} | Codigo: {$codigoRadicado}\n");
            return true;
        } catch (Exception $e) {
            $this->log(date('Y-m-d H:i:s') . " | FALLO-RESPUESTA | Para: {$para} | Codigo: {$codigoRadicado} | Error: {$e->getMessage()}\n");
            return false;
        }
    }

    // ─── Plantillas HTML ──────────────────────────────────────────────────────

    private function plantillaConfirmacion(
        string $nombre,
        string $codigo,
        string $tipo,
        string $asunto,
        string $vencimiento,
        string $host
    ): string {
        $nombre = htmlspecialchars($nombre ?: 'Ciudadano');
        $codigo = htmlspecialchars($codigo);
        $tipo   = htmlspecialchars($tipo);
        $asunto = htmlspecialchars($asunto);
        return "<!DOCTYPE html><html><head><meta charset='UTF-8'>
        <style>
            body{font-family:Arial,sans-serif;color:#333;margin:0;padding:0}
            .wrap{max-width:600px;margin:0 auto;padding:20px}
            .head{background:linear-gradient(135deg,#1e40af,#1e3a8a);color:#fff;padding:30px;text-align:center;border-radius:10px 10px 0 0}
            .body{background:#f9fafb;padding:30px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 10px 10px}
            .cbox{background:#1e40af;color:#fff;padding:20px;text-align:center;border-radius:8px;margin:20px 0}
            .cbox .cod{font-size:22px;font-weight:700;letter-spacing:2px}
            .det{background:#fff;padding:15px;border-radius:8px;margin:10px 0}
            .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:14px}
            .row:last-child{border-bottom:none}
            .row span:first-child{color:#6b7280}
            .row span:last-child{font-weight:600}
            .btn{display:inline-block;background:#1e40af;color:#fff!important;padding:12px 32px;text-decoration:none;border-radius:6px;margin-top:20px;font-size:14px;font-weight:600}
            .foot{text-align:center;padding:20px 0 0;color:#9ca3af;font-size:11px}
        </style></head><body>
        <div class='wrap'>
            <div class='head'><h1>Solicitud PQRS Radicada</h1><p style='opacity:.85;font-size:14px'>Sistema PQRS - Confirmacion</p></div>
            <div class='body'>
                <p>Estimado(a) <strong>{$nombre}</strong>,</p>
                <p>Su solicitud ha sido recibida exitosamente.</p>
                <div class='cbox'><div style='font-size:11px;opacity:.8;text-transform:uppercase'>Codigo de Radicado</div><div class='cod'>{$codigo}</div></div>
                <div class='det'>
                    <div class='row'><span>Tipo:</span><span>{$tipo}</span></div>
                    <div class='row'><span>Asunto:</span><span>{$asunto}</span></div>
                    <div class='row'><span>Fecha limite:</span><span>{$vencimiento}</span></div>
                </div>
                <p style='text-align:center'><a href='http://{$host}/SistemaPQRS/index.php?ruta=pqrs/consulta' class='btn'>Consultar mi Solicitud</a></p>
                <p style='font-size:12px;color:#9ca3af'>Guarde su codigo de radicado para consultar el estado de su solicitud.</p>
            </div>
            <div class='foot'><p>&copy; " . date('Y') . " Sistema PQRS</p><p>Ley 1755 de 2015 &middot; Ley 1437 de 2011</p></div>
        </div></body></html>";
    }

    private function plantillaRespuesta(
        string $nombre,
        string $codigo,
        string $tipo,
        string $asunto,
        string $respuesta,
        array  $estado,
        string $host
    ): string {
        $nombre   = htmlspecialchars($nombre ?: 'Ciudadano');
        $codigo   = htmlspecialchars($codigo);
        $tipo     = htmlspecialchars($tipo);
        $asunto   = htmlspecialchars($asunto);
        $respHtml = nl2br(htmlspecialchars($respuesta));
        return "<!DOCTYPE html><html><head><meta charset='UTF-8'>
        <style>
            body{font-family:Arial,sans-serif;color:#333;margin:0;padding:0}
            .wrap{max-width:600px;margin:0 auto;padding:20px}
            .head{background:linear-gradient(135deg,#1e40af,#1e3a8a);color:#fff;padding:30px;text-align:center;border-radius:10px 10px 0 0}
            .body{background:#f9fafb;padding:30px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 10px 10px}
            .cbox{background:#1e40af;color:#fff;padding:20px;text-align:center;border-radius:8px;margin:20px 0}
            .cbox .cod{font-size:22px;font-weight:700;letter-spacing:2px}
            .det{background:#fff;padding:15px;border-radius:8px;margin:10px 0}
            .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:14px}
            .row:last-child{border-bottom:none}
            .row span:first-child{color:#6b7280}
            .row span:last-child{font-weight:600}
            .resp-box{background:#fff;border-left:4px solid #1e40af;border-radius:8px;padding:20px;margin:16px 0;font-size:14px;line-height:1.7}
            .btn{display:inline-block;background:#1e40af;color:#fff!important;padding:12px 32px;text-decoration:none;border-radius:6px;margin-top:20px;font-size:14px;font-weight:600}
            .foot{text-align:center;padding:20px 0 0;color:#9ca3af;font-size:11px}
        </style></head><body>
        <div class='wrap'>
            <div class='head'><div style='font-size:36px'>{$estado['icon']}</div><h1>Su solicitud ha sido atendida</h1></div>
            <div class='body'>
                <p>Estimado(a) <strong>{$nombre}</strong>,</p>
                <div class='cbox'><div style='font-size:11px;opacity:.8;text-transform:uppercase'>Codigo de Radicado</div><div class='cod'>{$codigo}</div></div>
                <div class='det'>
                    <div class='row'><span>Tipo:</span><span>{$tipo}</span></div>
                    <div class='row'><span>Asunto:</span><span>{$asunto}</span></div>
                    <div class='row'><span>Fecha de respuesta:</span><span>" . date('d/m/Y H:i') . "</span></div>
                    <div class='row'><span>Estado:</span><span style='color:{$estado['color']};font-weight:700'>{$estado['texto']}</span></div>
                </div>
                <div class='resp-box'>{$respHtml}</div>
                <p style='text-align:center'><a href='http://{$host}/SistemaPQRS/index.php?ruta=pqrs/consulta' class='btn'>Ver mi Solicitud</a></p>
            </div>
            <div class='foot'><p>&copy; " . date('Y') . " Sistema PQRS</p><p>Ley 1755 de 2015 &middot; Ley 1437 de 2011</p></div>
        </div></body></html>";
    }
    public function enviarCorreoRecuperacion(string $para, string $nombre, string $usuario, string $urlReset): bool
    {
        try {
            $mail = $this->crearMailer();
            $mail->addAddress($para, $nombre ?: 'Administrador');
            $mail->Subject = 'Recuperación de Contraseña - Sistema PQRS';
            $mail->isHTML(true);

            $html = "
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

            $mail->Body = $html;
            $mail->AltBody = "Hola $nombre,\n\nRecibimos una solicitud para restablecer su contraseña.\n\nHaga clic aquí: $urlReset\n\nEl enlace expirará en 1 hora.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            $this->log(date('Y-m-d H:i:s') . " | FALLO-RECUPERACION | Para: $para | Error: " . $e->getMessage() . "\n");
            return false;
        }
    }
}
