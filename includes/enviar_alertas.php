<?php
/**
 * Sistema de Alertas Automáticas por Email
 * Envía notificaciones de PQRS próximas a vencer o vencidas
 * al correo configurado en la tabla configuracion_sistema
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/funciones.php';

// Cargar PHPMailer desde el vendor existente
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía alertas de PQRS vencidas o próximas a vencer
 */
function enviarAlertasEmail() {
    $con = conexion();
    if (!$con) {
        echo "Error: No se pudo conectar a la base de datos\n";
        return false;
    }

    // Obtener correo de notificaciones desde configuración
    $correo_destino = obtenerCorreoNotificaciones();

    // Obtener PQRS próximas a vencer (5 días o menos) o vencidas
    $query = "SELECT p.*, u.nombre_completo, u.correo_electronico,
                     DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
              FROM pqrs p
              LEFT JOIN usuario u ON p.usuario_id = u.id
              WHERE p.estado NOT IN ('RESUELTO', 'RECHAZADO')
                AND p.fecha_vencimiento IS NOT NULL
                AND DATEDIFF(p.fecha_vencimiento, CURDATE()) <= 5
              ORDER BY dias_restantes ASC";

    $result = mysqli_query($con, $query);
    if (!$result || mysqli_num_rows($result) === 0) {
        echo "No hay PQRS próximas a vencer o vencidas.\n";
        mysqli_close($con);
        return true;
    }

    $pqrs_alerta = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pqrs_alerta[] = $row;
    }
    mysqli_close($con);

    // Construir el cuerpo del email
    $html = construirEmailAlertas($pqrs_alerta);

    // Enviar email
    $enviado = enviarEmail($correo_destino, 'Alerta PQRS - Solicitudes Próximas a Vencer', $html);

    if ($enviado) {
        echo "Alertas enviadas exitosamente a: {$correo_destino}\n";
        echo "Total de PQRS notificadas: " . count($pqrs_alerta) . "\n";
    } else {
        echo "Error al enviar alertas.\n";
    }

    return $enviado;
}

/**
 * Construye el HTML del email de alertas
 */
function construirEmailAlertas($pqrs_list) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: #1e40af; color: white; padding: 20px; text-align: center; }
            .header h1 { margin: 0; font-size: 20px; }
            .content { padding: 20px; }
            .alerta { border-left: 4px solid; padding: 15px; margin-bottom: 15px; background: #f9fafb; }
            .alerta.vencida { border-color: #dc2626; background: #fef2f2; }
            .alerta.critica { border-color: #ea580c; background: #fff7ed; }
            .alerta.advertencia { border-color: #ca8a04; background: #fefce8; }
            .codigo { font-weight: bold; color: #1e40af; font-size: 16px; }
            .estado { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
            .estado-pendiente { background: #fef3c7; color: #92400e; }
            .estado-proceso { background: #dbeafe; color: #1e40af; }
            .dias { font-weight: bold; font-size: 18px; }
            .dias.vencida { color: #dc2626; }
            .dias.critica { color: #ea580c; }
            .dias.advertencia { color: #ca8a04; }
            .footer { background: #f3f4f6; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            table { width: 100%; margin-top: 10px; }
            td { padding: 5px 0; }
            .label { color: #666; font-size: 12px; }
            .value { font-weight: 500; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>⚠️ Alerta de Vencimiento PQRS</h1>
                <p>Sistema de Gestión PQRS</p>
            </div>
            <div class="content">
                <p>Se han detectado las siguientes solicitudes que requieren atención inmediata:</p>';

    foreach ($pqrs_list as $pqrs) {
        $dias = intval($pqrs['dias_restantes']);

        if ($dias < 0) {
            $clase = 'vencida';
            $texto_dias = 'VENCIDA hace ' . abs($dias) . ' días';
            $titulo = '🚨 SOLICITUD VENCIDA';
        } elseif ($dias <= 2) {
            $clase = 'critica';
            $texto_dias = $dias . ' días restantes';
            $titulo = '⚠️ URGENTE';
        } else {
            $clase = 'advertencia';
            $texto_dias = $dias . ' días restantes';
            $titulo = '⏰ Próxima a vencer';
        }

        $estadoClass = strtolower(str_replace('_', '-', $pqrs['estado']));
        $estadoLabels = [
            'PENDIENTE' => 'Pendiente',
            'EN_PROCESO' => 'En Proceso',
            'RESUELTO' => 'Resuelto',
            'RECHAZADO' => 'Rechazado'
        ];

        $html .= '
                <div class="alerta ' . $clase . '">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span class="codigo">' . htmlspecialchars($pqrs['codigo_radicado']) . '</span>
                        <span class="dias ' . $clase . '">' . $texto_dias . '</span>
                    </div>
                    <div style="margin-bottom:8px;"><strong>' . $titulo . '</strong></div>
                    <table>
                        <tr>
                            <td class="label">Asunto:</td>
                            <td class="value">' . htmlspecialchars($pqrs['asunto']) . '</td>
                        </tr>
                        <tr>
                            <td class="label">Tipo:</td>
                            <td class="value">' . ucfirst($pqrs['tipo_solicitud']) . '</td>
                        </tr>
                        <tr>
                            <td class="label">Estado:</td>
                            <td><span class="estado estado-' . $estadoClass . '">' . ($estadoLabels[$pqrs['estado']] ?? $pqrs['estado']) . '</span></td>
                        </tr>
                        <tr>
                            <td class="label">Solicitante:</td>
                            <td class="value">' . htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo') . '</td>
                        </tr>
                        <tr>
                            <td class="label">Vence:</td>
                            <td class="value">' . date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])) . '</td>
                        </tr>
                    </table>
                </div>';
    }

    $html .= '
            </div>
            <div class="footer">
                <p>Este es un mensaje automático del Sistema PQRS</p>
                <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
            </div>
        </div>
    </body>
    </html>';

    return $html;
}

/**
 * Envía un email usando PHPMailer
 */
function enviarEmail($destinatario, $asunto, $html) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP (ajusta según tu proveedor)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'santiagolizcanosuarez@gmail.com';
        $mail->Password = 'tu_contraseña_app'; // Usa contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Configuración del email
        $mail->setFrom('santiagolizcanosuarez@gmail.com', 'Sistema PQRS');
        $mail->addAddress($destinatario);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $asunto;
        $mail->Body = $html;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Error enviando email: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Guarda alerta en tabla alerta_vencimiento
 */
function guardarAlerta($pqrs_id, $dias_restantes, $nivel) {
    $con = conexion();
    if (!$con) return false;

    // Verificar si ya existe alerta no enviada para esta PQRS
    $check = $con->prepare("SELECT id FROM alerta_vencimiento WHERE pqrs_id = ? AND notificacion_enviada = FALSE");
    $check->bind_param("i", $pqrs_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        mysqli_close($con);
        return true; // Ya existe alerta pendiente
    }
    $check->close();

    // Insertar nueva alerta
    $stmt = $con->prepare("INSERT INTO alerta_vencimiento (pqrs_id, dias_restantes, nivel_alerta, notificacion_enviada) VALUES (?, ?, ?, FALSE)");
    $stmt->bind_param("iis", $pqrs_id, $dias_restantes, $nivel);
    $result = $stmt->execute();
    $stmt->close();
    mysqli_close($con);

    return $result;
}

// Si se ejecuta directamente
if (php_sapi_name() === 'cli' || isset($_GET['ejecutar'])) {
    enviarAlertasEmail();
}