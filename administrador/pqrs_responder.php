<?php
/* HU-Detalle y Respuesta: Formulario para responder PQRS y cambiar estado
   Envía correo al ciudadano si tiene correo registrado (Opción B)
   Usa SendGrid API (HTTP) en lugar de PHPMailer SMTP
*/

include '../includes/verificar_sesion.php';
include '../config/conexion.php';
include '../includes/funciones.php';

// ─── SendGrid ────────────────────────────────────────────────────────────────
use SendGrid\Mail\Mail;
require_once __DIR__ . '/../vendor/autoload.php';

// ─── HELPER: Log seguro (evita errores de permisos en cloud) ─────────────────
function logEmail(string $mensaje): void {
    $paths = [
        __DIR__ . '/../logs/email_log.txt',
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
 * Envía correo de notificación de respuesta al ciudadano usando SendGrid API.
 * Solo se llama si el usuario tiene correo registrado (Opción B).
 */
function enviarCorreoRespuesta(
    string $para,
    string $nombre,
    string $codigo_radicado,
    string $tipo_pqrs,
    string $asunto_solicitud,
    string $contenido_respuesta,
    string $nuevo_estado,
    string $host
): bool {

    // Leer configuración desde variables de entorno de Railway
    $sendgrid_api_key = $_ENV['SENDGRID_API_KEY'] ?? '';
    $from_email       = $_ENV['SENDGRID_FROM_EMAIL'] ?? 'santiagolizcanosuarez@gmail.com';
    $from_name        = $_ENV['SENDGRID_FROM_NAME']  ?? 'Sistema PQRS';

    if (empty($sendgrid_api_key)) {
        logEmail(date('Y-m-d H:i:s') . " | FALLO-RESPUESTA | SendGrid API Key no configurada\n");
        return false;
    }

    // Colores y texto según estado
    $estadoColores = [
        'RESUELTO'   => ['color' => '#059669', 'bg' => '#d1fae5', 'texto' => 'Resuelto',   'icon' => '✓'],
        'RECHAZADO'  => ['color' => '#dc2626', 'bg' => '#fee2e2', 'texto' => 'Rechazado',  'icon' => '✗'],
        'EN_PROCESO' => ['color' => '#1e40af', 'bg' => '#dbeafe', 'texto' => 'En Proceso', 'icon' => '↻'],
        'PENDIENTE'  => ['color' => '#d97706', 'bg' => '#fef3c7', 'texto' => 'Pendiente',  'icon' => '◷'],
    ];
    $estado = $estadoColores[$nuevo_estado] ?? $estadoColores['PENDIENTE'];

    $tipoLabel = [
        'peticion'   => 'Petición',
        'queja'      => 'Queja',
        'reclamo'    => 'Reclamo',
        'sugerencia' => 'Sugerencia',
        'denuncia'   => 'Denuncia',
    ][$tipo_pqrs] ?? ucfirst($tipo_pqrs);

    try {
        $email = new Mail();
        $email->setFrom($from_email, $from_name);
        $email->addTo($para, $nombre ?: 'Ciudadano');
        $email->setSubject("Respuesta a su solicitud PQRS - $codigo_radicado");

        $respuesta_html = nl2br(htmlspecialchars($contenido_respuesta));

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body{font-family:Arial,sans-serif;line-height:1.6;color:#333;margin:0;padding:0}
                .wrap{max-width:600px;margin:0 auto;padding:20px}
                .head{background:linear-gradient(135deg,#1e40af,#1e3a8a);color:#fff;padding:30px;text-align:center;border-radius:10px 10px 0 0}
                .head h1{margin:0;font-size:22px}
                .body{background:#f9fafb;padding:30px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 10px 10px}
                .cbox{background:#1e40af;color:#fff;padding:20px;text-align:center;border-radius:8px;margin:20px 0}
                .cbox .lbl{font-size:11px;text-transform:uppercase;letter-spacing:1px;opacity:.8}
                .cbox .cod{font-size:24px;font-weight:700;font-family:'Courier New',monospace;margin:10px 0;letter-spacing:2px}
                .estado-box{padding:12px 20px;border-radius:8px;margin:16px 0;display:inline-block;font-weight:700;font-size:15px}
                .respuesta-box{background:#fff;border:1px solid #e5e7eb;border-left:4px solid #1e40af;border-radius:8px;padding:20px;margin:16px 0}
                .respuesta-box h3{margin:0 0 12px;font-size:14px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em}
                .respuesta-texto{font-size:14px;color:#374151;line-height:1.7}
                .det{background:#fff;padding:15px 20px;border-radius:8px;margin:10px 0}
                .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:14px}
                .row:last-child{border-bottom:none}
                .row span:first-child{color:#6b7280}
                .row span:last-child{font-weight:600}
                .btn{display:inline-block;background:#1e40af;color:#fff!important;padding:12px 32px;text-decoration:none;border-radius:6px;margin-top:20px;font-size:14px;font-weight:600}
                .foot{text-align:center;padding:20px 0 0;color:#9ca3af;font-size:11px}
                .aviso{background:#fef3c7;border:1px solid #fcd34d;border-radius:6px;padding:12px 16px;font-size:12px;color:#92400e;margin-top:16px}
            </style>
        </head>
        <body>
        <div class='wrap'>
            <div class='head'>
                <div style='font-size:40px;margin-bottom:10px'>{$estado['icon']}</div>
                <h1>Su solicitud ha sido atendida</h1>
                <p style='margin:5px 0 0;opacity:.85;font-size:14px'>Sistema PQRS — Respuesta Oficial</p>
            </div>
            <div class='body'>
                <p>Estimado(a) <strong>" . htmlspecialchars($nombre ?: 'Ciudadano') . "</strong>,</p>
                <p>Le informamos que su solicitud ha recibido una respuesta oficial por parte de nuestro equipo.</p>

                <div class='cbox'>
                    <div class='lbl'>Código de Radicado</div>
                    <div class='cod'>" . htmlspecialchars($codigo_radicado) . "</div>
                </div>

                <div class='det'>
                    <div class='row'><span>Tipo de solicitud:</span><span>$tipoLabel</span></div>
                    <div class='row'><span>Asunto:</span><span>" . htmlspecialchars($asunto_solicitud) . "</span></div>
                    <div class='row'><span>Fecha de respuesta:</span><span>" . date('d/m/Y H:i:s') . "</span></div>
                    <div class='row'>
                        <span>Estado actualizado:</span>
                        <span style='color:{$estado['color']};font-weight:700'>{$estado['icon']} {$estado['texto']}</span>
                    </div>
                </div>

                <div class='respuesta-box'>
                    <h3><i>📋</i> Respuesta del Administrador</h3>
                    <div class='respuesta-texto'>$respuesta_html</div>
                </div>

                <p style='text-align:center'>
                    <a href='http://{$host}/pqrs/consulta_pqrs.php?codigo=" . urlencode($codigo_radicado) . "' class='btn'>
                        Ver mi Solicitud en el Portal
                    </a>
                </p>

                <div class='aviso'>
                    ⚠️ Si no está de acuerdo con la respuesta, puede presentar una nueva solicitud de revisión citando el código de radicado.
                </div>

                <p style='font-size:12px;color:#9ca3af;margin-top:24px'>
                    Mensaje automático generado por el Sistema PQRS. Por favor no responda a este correo.
                </p>
            </div>
            <div class='foot'>
                <p>© " . date('Y') . " Sistema PQRS — Todos los derechos reservados</p>
                <p>Cumplimiento legal según Ley 1755 de 2015 y Ley 1437 de 2011</p>
            </div>
        </div>
        </body>
        </html>";

        $email->addContent("text/html", $html);
        $email->addContent("text/plain", 
            "Su solicitud PQRS ha sido atendida.\n\n"
            . "Código: $codigo_radicado\n"
            . "Tipo: $tipoLabel\n"
            . "Asunto: " . htmlspecialchars($asunto_solicitud) . "\n"
            . "Estado: {$estado['texto']}\n"
            . "Fecha de respuesta: " . date('d/m/Y H:i:s') . "\n\n"
            . "Respuesta:\n$contenido_respuesta\n\n"
            . "Consulte en: http://{$host}/pqrs/consulta_pqrs.php?codigo=" . urlencode($codigo_radicado)
        );

        $sendgrid = new \SendGrid($sendgrid_api_key);
        $response = $sendgrid->send($email);
        $statusCode = $response->statusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        } else {
            logEmail(date('Y-m-d H:i:s') . " | FALLO-RESPUESTA-SENDGRID | Para: $para | Status: $statusCode | Body: " . $response->body() . "\n");
            return false;
        }

    } catch (\Exception $e) {
        logEmail(date('Y-m-d H:i:s') . " | FALLO-RESPUESTA-EXCEPTION | Para: $para | Error: " . $e->getMessage() . "\n");
        return false;
    }
}
// ─────────────────────────────────────────────────────────────────────────────


$con = conexion();
$id  = intval($_GET['id'] ?? 0);
$mensaje_exito = '';
$mensaje_error = '';
$correo_notificado = false;

if (!$id) {
    header('Location: pqrs.php');
    exit;
}

// ── Procesar formulario ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $contenido          = trim($_POST['contenido']          ?? '');
    $nuevo_estado       = trim($_POST['nuevo_estado']       ?? '');
    $es_visible_publico = isset($_POST['es_visible_publico']) ? 1 : 0;

    if (empty($contenido)) {
        $mensaje_error = 'El contenido de la respuesta es obligatorio.';

    } else {

        // 1. Guardar respuesta visible al ciudadano
        if ($es_visible_publico) {
            $stmt = $con->prepare(
                "UPDATE pqrs
                 SET respuesta_administrador = ?,
                     fecha_respuesta = NOW(),
                     administrador_id = ?
                 WHERE id = ?"
            );
            $stmt->bind_param("sii", $contenido, $adminId, $id);
            $stmt->execute();
        }

        // 2. Cambiar estado si se seleccionó uno nuevo
        if (!empty($nuevo_estado)) {
            $stmt_estado = $con->prepare(
                "UPDATE pqrs SET estado = ?, fecha_actualizacion = NOW() WHERE id = ?"
            );
            $stmt_estado->bind_param("si", $nuevo_estado, $id);
            $stmt_estado->execute();

            registrarAccion('CAMBIO_ESTADO', "Estado cambiado a: $nuevo_estado", $id);
        }

        // 3. Registrar respuesta en historial
        $desc_respuesta = "Respuesta enviada" . ($es_visible_publico ? " (pública)" : " (interna)");
        registrarAccion('RESPUESTA', $desc_respuesta, $id);

        // 4. OPCIÓN B: Enviar correo si el usuario tiene correo registrado
        //    Obtener datos del ciudadano para el correo
        $stmt_datos = $con->prepare(
            "SELECT p.codigo_radicado, p.tipo_solicitud, p.asunto,
                    p.estado,
                    u.nombre_completo, u.correo_electronico, u.correo_corporativo,
                    u.nombre_representante, u.tipo_persona
             FROM pqrs p
             LEFT JOIN usuario u ON p.usuario_id = u.id
             WHERE p.id = ?"
        );
        $stmt_datos->bind_param("i", $id);
        $stmt_datos->execute();
        $datos = $stmt_datos->get_result()->fetch_assoc();

        if ($datos) {
            // Determinar correo destino (natural → correo_electronico, jurídica → correo_corporativo)
            $correoDestino = null;
            if ($datos['tipo_persona'] === 'natural' && !empty($datos['correo_electronico'])) {
                $correoDestino = $datos['correo_electronico'];
            } elseif ($datos['tipo_persona'] === 'juridica' && !empty($datos['correo_corporativo'])) {
                $correoDestino = $datos['correo_corporativo'];
            }
            // Anónima: no tiene correo → no se envía

            // Estado final para el correo
            $estadoFinalCorreo = !empty($nuevo_estado) ? $nuevo_estado : $datos['estado'];

            // Nombre del solicitante
            $nombreCiudadano = $datos['nombre_completo'] ?? $datos['nombre_representante'] ?? '';

            if (!empty($correoDestino) && $es_visible_publico) {
                // Solo se envía si la respuesta es pública (visible al ciudadano)
                $correo_notificado = enviarCorreoRespuesta(
                    $correoDestino,
                    $nombreCiudadano,
                    $datos['codigo_radicado'],
                    $datos['tipo_solicitud'],
                    $datos['asunto'],
                    $contenido,
                    $estadoFinalCorreo,
                    $_SERVER['HTTP_HOST']
                );

                // Log seguro (no genera warnings)
                logEmail(
                    date('Y-m-d H:i:s') . " | " . ($correo_notificado ? "ENVIADO-RESPUESTA" : "FALLO-RESPUESTA")
                    . " | Para: $correoDestino | Codigo: {$datos['codigo_radicado']}\n"
                );
            }
        }

        $mensaje_exito = 'Respuesta registrada exitosamente.'
            . ($correo_notificado ? ' El ciudadano ha sido notificado por correo.' : '');

        mysqli_close($con);
        header("Refresh: 2; url=pqrs_ver.php?id=$id");
        $con = conexion();
    }
}

// ── Obtener datos de la PQRS para mostrar el formulario ──────────────────────
$query = "SELECT p.*, u.nombre_completo, u.correo_electronico, u.tipo_persona,
                 DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
          FROM pqrs p
          LEFT JOIN usuario u ON p.usuario_id = u.id
          WHERE p.id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    mysqli_close($con);
    header('Location: pqrs.php?error=not_found');
    exit;
}

$pqrs = $result->fetch_assoc();
mysqli_close($con);

$tipoLabels = [
    'peticion'   => 'Petición',
    'queja'      => 'Queja',
    'reclamo'    => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia'   => 'Denuncia',
];
$estadoLabels = [
    'PENDIENTE'  => 'Pendiente',
    'EN_PROCESO' => 'En Proceso',
    'RESUELTO'   => 'Resuelto',
    'RECHAZADO'  => 'Rechazado',
];

// Determinar si el ciudadano recibirá correo (para mostrar indicador en UI)
$tienCorreo = !empty($pqrs['correo_electronico']) && $pqrs['tipo_persona'] !== 'anonima';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder PQRS <?php echo htmlspecialchars($pqrs['codigo_radicado']); ?> - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* ── Estilos específicos de esta vista ── */
        .detalle-nav { margin-bottom: var(--space-6); }

        .btn-volver-detalle {
            display: inline-flex;
            align-items: center;
            gap: var(--space-2);
            color: var(--color-gray-500);
            font-size: var(--font-size-sm);
            font-weight: 500;
            padding: var(--space-2) var(--space-4);
            border: 1px solid var(--color-gray-200);
            border-radius: var(--radius-lg);
            background: var(--color-white);
            transition: all var(--transition-fast);
            text-decoration: none;
        }
        .btn-volver-detalle:hover { border-color: var(--color-primary); color: var(--color-primary); background: #eff6ff; }

        /* Alertas */
        .alerta {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-4) var(--space-5);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-5);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        .alerta i { font-size: 1.25rem; flex-shrink: 0; }
        .alerta-exito { background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #059669; color: #065f46; }
        .alerta-error { background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--color-danger); color: #991b1b; }

        /* Grid principal */
        .responder-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--space-6);
        }
        @media (min-width: 1024px) {
            .responder-grid { grid-template-columns: 1fr 340px; }
        }

        /* Tarjetas */
        .detalle-card {
            background: var(--color-white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--color-gray-100);
            overflow: hidden;
            margin-bottom: var(--space-5);
        }
        .detalle-card:last-child { margin-bottom: 0; }
        .detalle-card-header {
            padding: var(--space-4) var(--space-5);
            border-bottom: 1px solid var(--color-gray-100);
            background: var(--color-gray-50);
        }
        .detalle-card-header h2 {
            font-size: var(--font-size-base);
            font-weight: 700;
            color: var(--color-gray-800);
            margin: 0;
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        .detalle-card-header h2 i { color: var(--color-primary); }
        .detalle-card-body { padding: var(--space-5); }

        /* Textarea grande */
        .respuesta-textarea {
            min-height: 220px;
            font-family: inherit;
            resize: vertical;
        }

        /* Indicador de correo */
        .correo-indicator {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            font-size: var(--font-size-xs);
            font-weight: 500;
            margin-top: var(--space-3);
        }
        .correo-indicator.si  { background: #f0fdf4; border: 1px solid #bbf7d0; color: #065f46; }
        .correo-indicator.no  { background: var(--color-gray-50); border: 1px solid var(--color-gray-200); color: var(--color-gray-500); }
        .correo-indicator i   { font-size: 1rem; flex-shrink: 0; }

        /* Form actions */
        .form-actions-responder {
            display: flex;
            gap: var(--space-3);
            justify-content: flex-end;
            margin-top: var(--space-6);
            padding-top: var(--space-5);
            border-top: 1px solid var(--color-gray-100);
        }

        /* Info items sidebar */
        .info-item { display: flex; flex-direction: column; gap: 2px; padding: var(--space-3) 0; border-bottom: 1px solid var(--color-gray-100); }
        .info-item:last-child { border-bottom: none; }
        .info-item label { font-size: var(--font-size-xs); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--color-gray-400); }
        .info-item span  { font-size: var(--font-size-sm); color: var(--color-gray-800); font-weight: 500; }
        .info-item code  { background: var(--color-gray-100); padding: 2px 6px; border-radius: var(--radius-sm); font-family: 'Courier New', monospace; font-size: var(--font-size-xs); color: var(--color-primary); font-weight: 700; }
        .info-divider    { border: none; border-top: 2px solid var(--color-gray-100); margin: var(--space-2) 0; }

        /* Badges de tipo */
        .tipo-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        .tipo-peticion   { background: #dbeafe; color: #1e40af; }
        .tipo-queja      { background: #fee2e2; color: #991b1b; }
        .tipo-reclamo    { background: #fef3c7; color: #92400e; }
        .tipo-sugerencia { background: #d1fae5; color: #065f46; }
        .tipo-denuncia   { background: #ede9fe; color: #5b21b6; }

        /* Días restantes */
        .text-danger  { color: var(--color-danger); font-weight: 600; }
        .text-warning { color: var(--color-accent);  font-weight: 600; }

        /* Plantillas */
        .plantillas-lista  { display: flex; flex-direction: column; gap: var(--space-2); }
        .plantilla-btn {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            padding: var(--space-2) var(--space-3);
            border: 1px solid var(--color-gray-200);
            border-radius: var(--radius-md);
            background: var(--color-white);
            color: var(--color-gray-600);
            font-size: var(--font-size-sm);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            text-align: left;
        }
        .plantilla-btn:hover { border-color: var(--color-primary); color: var(--color-primary); background: #eff6ff; }
        .plantilla-btn i { font-size: 1rem; flex-shrink: 0; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="dashboard-section">
        <div class="container">

            <!-- Navegación -->
            <div class="detalle-nav">
                <a href="pqrs_ver.php?id=<?php echo $id; ?>" class="btn-volver-detalle">
                    <i class="bi bi-arrow-left"></i>
                    Volver al detalle
                </a>
            </div>

            <!-- Mensajes -->
            <?php if ($mensaje_exito): ?>
            <div class="alerta alerta-exito">
                <i class="bi bi-check-circle-fill"></i>
                <?php echo htmlspecialchars($mensaje_exito); ?>
            </div>
            <?php endif; ?>

            <?php if ($mensaje_error): ?>
            <div class="alerta alerta-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?php echo htmlspecialchars($mensaje_error); ?>
            </div>
            <?php endif; ?>

            <div class="responder-grid">

                <!-- ── Formulario de respuesta ── -->
                <div class="responder-main">
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-reply"></i> Responder Solicitud</h2>
                        </div>
                        <div class="detalle-card-body">
                            <form method="POST" action="" class="responder-form">

                                <!-- Contenido -->
                                <div class="form-grupo">
                                    <label class="form-label">
                                        <i class="bi bi-chat-left-text" style="margin-right:4px;color:var(--color-primary)"></i>
                                        Contenido de la Respuesta
                                        <span class="requerido">*</span>
                                    </label>
                                    <textarea
                                        name="contenido"
                                        class="form-textarea respuesta-textarea"
                                        rows="10"
                                        placeholder="Escriba aquí la respuesta formal a la solicitud..."
                                        required
                                    ><?php echo htmlspecialchars($_POST['contenido'] ?? ''); ?></textarea>
                                    <p class="form-ayuda">Esta respuesta quedará registrada en el sistema como respuesta oficial.</p>
                                </div>

                                <!-- Cambio de estado -->
                                <div class="form-grupo">
                                    <label class="form-label">
                                        <i class="bi bi-flag" style="margin-right:4px;color:var(--color-primary)"></i>
                                        Cambiar Estado
                                    </label>
                                    <select name="nuevo_estado" class="form-select">
                                        <option value="">
                                            Mantener estado actual
                                            (<?php echo $estadoLabels[$pqrs['estado']] ?? $pqrs['estado']; ?>)
                                        </option>
                                        <?php if ($pqrs['estado'] === 'PENDIENTE'): ?>
                                            <option value="EN_PROCESO">Cambiar a: En Proceso</option>
                                        <?php endif; ?>
                                        <?php if (in_array($pqrs['estado'], ['PENDIENTE', 'EN_PROCESO'])): ?>
                                            <option value="RESUELTO">Cambiar a: Resuelto</option>
                                            <option value="RECHAZADO">Cambiar a: Rechazado</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <!-- Visibilidad -->
                                <div class="form-grupo">
                                    <label class="checkbox-container" style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;padding:.75rem;background:var(--color-gray-50);border-radius:var(--radius-md);border:1px solid var(--color-gray-200);">
                                        <input type="checkbox" name="es_visible_publico" value="1" checked
                                               style="width:18px;height:18px;margin-top:2px;accent-color:var(--color-primary);"
                                               onchange="actualizarIndicadorCorreo(this.checked)">
                                        <span class="checkbox-label">
                                            <strong>Hacer visible al ciudadano</strong><br>
                                            <small style="color:var(--color-gray-500)">
                                                Si está marcado, el ciudadano podrá ver esta respuesta en el portal de consulta.
                                            </small>
                                        </span>
                                    </label>

                                    <!-- Indicador de correo (Opción B) -->
                                    <?php if ($tienCorreo): ?>
                                    <div class="correo-indicator si" id="indicadorCorreo">
                                        <i class="bi bi-envelope-check-fill"></i>
                                        <span>
                                            Se enviará notificación automática por correo a
                                            <strong><?php echo htmlspecialchars($pqrs['correo_electronico']); ?></strong>
                                        </span>
                                    </div>
                                    <?php else: ?>
                                    <div class="correo-indicator no" id="indicadorCorreo">
                                        <i class="bi bi-envelope-slash"></i>
                                        <span>
                                            <?php echo $pqrs['tipo_persona'] === 'anonima'
                                                ? 'Solicitud anónima — no se enviará correo.'
                                                : 'El ciudadano no registró correo — no se enviará notificación.'; ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Acciones -->
                                <div class="form-actions-responder">
                                    <a href="pqrs_ver.php?id=<?php echo $id; ?>" class="btn-volver">
                                        <i class="bi bi-x-circle"></i>
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn-enviar">
                                        <i class="bi bi-send"></i>
                                        Enviar Respuesta
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div><!-- /responder-main -->

                <!-- ── Sidebar ── -->
                <div class="responder-sidebar">

                    <!-- Resumen -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-file-text"></i> Resumen</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="info-item">
                                <label>Código</label>
                                <span><code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code></span>
                            </div>
                            <div class="info-item">
                                <label>Tipo</label>
                                <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                                    <?php echo $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']); ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Estado Actual</label>
                                <span class="estado-tag estado-<?php echo strtolower(str_replace('_', '-', $pqrs['estado'])); ?>">
                                    <?php echo $estadoLabels[$pqrs['estado']] ?? $pqrs['estado']; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Solicitante</label>
                                <span><?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo'); ?></span>
                            </div>
                            <?php if (!empty($pqrs['correo_electronico'])): ?>
                            <div class="info-item">
                                <label>Correo</label>
                                <span style="font-size:var(--font-size-xs);word-break:break-all;">
                                    <?php echo htmlspecialchars($pqrs['correo_electronico']); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <hr class="info-divider">
                            <div class="info-item">
                                <label>Asunto</label>
                                <span><?php echo htmlspecialchars($pqrs['asunto']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Plazos -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-clock-history"></i> Plazos</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="info-item">
                                <label>Fecha Radicación</label>
                                <span><?php echo date('d/m/Y', strtotime($pqrs['fecha_radicacion'])); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Fecha Vencimiento</label>
                                <span><?php echo $pqrs['fecha_vencimiento'] ? date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])) : 'N/A'; ?></span>
                            </div>
                            <?php
                            $dr = $pqrs['dias_restantes'];
                            if ($dr !== null && !in_array($pqrs['estado'], ['RESUELTO','RECHAZADO'])): ?>
                            <div class="info-item">
                                <label>Días Restantes</label>
                                <span class="<?php echo $dr < 0 ? 'text-danger' : ($dr <= 5 ? 'text-warning' : ''); ?>">
                                    <?php echo $dr < 0
                                        ? 'Vencida hace ' . abs($dr) . ' días'
                                        : $dr . ' días restantes'; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Plantillas -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-file-earmark-text"></i> Plantillas</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="plantillas-lista">
                                <button type="button" class="plantilla-btn" onclick="insertarPlantilla('recibido')">
                                    <i class="bi bi-check2-circle"></i> Acuse de Recibido
                                </button>
                                <button type="button" class="plantilla-btn" onclick="insertarPlantilla('proceso')">
                                    <i class="bi bi-arrow-repeat"></i> En Proceso
                                </button>
                                <button type="button" class="plantilla-btn" onclick="insertarPlantilla('resuelto')">
                                    <i class="bi bi-check-circle"></i> Solicitud Resuelta
                                </button>
                                <button type="button" class="plantilla-btn" onclick="insertarPlantilla('rechazado')">
                                    <i class="bi bi-x-circle"></i> Solicitud Rechazada
                                </button>
                            </div>
                        </div>
                    </div>

                </div><!-- /responder-sidebar -->

            </div><!-- /responder-grid -->
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script>
    // ── Plantillas de respuesta ────────────────────────────────────
    const codigo = '<?php echo addslashes($pqrs['codigo_radicado']); ?>';
    const plantillas = {
        recibido: `Estimado(a) ciudadano(a),\n\nNos permitimos informarle que su solicitud con radicado ${codigo} ha sido recibida exitosamente en nuestra entidad.\n\nSu solicitud será atendida dentro de los términos legales establecidos.\n\nCordialmente,\nSistema PQRS`,
        proceso:  `Estimado(a) ciudadano(a),\n\nLe informamos que su solicitud con radicado ${codigo} se encuentra actualmente en proceso de gestión.\n\nNuestro equipo está trabajando para darle respuesta en el menor tiempo posible.\n\nCordialmente,\nSistema PQRS`,
        resuelto: `Estimado(a) ciudadano(a),\n\nNos complace informarle que su solicitud con radicado ${codigo} ha sido resuelta satisfactoriamente.\n\n[Incluir aquí los detalles de la resolución]\n\nSi tiene alguna pregunta adicional, no dude en contactarnos.\n\nCordialmente,\nSistema PQRS`,
        rechazado:`Estimado(a) ciudadano(a),\n\nLamentamos informarle que su solicitud con radicado ${codigo} no ha podido ser tramitada por las siguientes razones:\n\n[Especificar las razones del rechazo]\n\nSi considera que esta decisión es incorrecta, puede presentar una nueva solicitud con la documentación adecuada.\n\nCordialmente,\nSistema PQRS`
    };

    function insertarPlantilla(tipo) {
        const textarea = document.querySelector('.respuesta-textarea');
        if (!textarea || !plantillas[tipo]) return;
        if (textarea.value && !confirm('¿Desea reemplazar el contenido actual con la plantilla?')) return;
        textarea.value = plantillas[tipo];
        textarea.focus();
    }

    // ── Indicador de correo según visibilidad ──────────────────────
    const tienCorreo = <?php echo $tienCorreo ? 'true' : 'false'; ?>;

    function actualizarIndicadorCorreo(esVisible) {
        const indicador = document.getElementById('indicadorCorreo');
        if (!indicador || !tienCorreo) return;

        if (esVisible) {
            indicador.className = 'correo-indicator si';
            indicador.innerHTML = `
                <i class="bi bi-envelope-check-fill"></i>
                <span>Se enviará notificación automática por correo al ciudadano</span>`;
        } else {
            indicador.className = 'correo-indicator no';
            indicador.innerHTML = `
                <i class="bi bi-envelope-slash"></i>
                <span>Respuesta interna — no se enviará correo al ciudadano</span>`;
        }
    }
    </script>

</body>
</html>