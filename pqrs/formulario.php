<?php
/**
 * HU-04 + HU-05: Formulario Adaptable + Generación de Código + Envío de Correo
 * Usa PHPMailer (SMTP) en lugar de mail() nativo
 */

require_once '../config/conexion.php';

// ─── HELPER PHPMailer ──────────────────────────────────────────────────────────
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Envía el correo de confirmación usando PHPMailer + SMTP
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

    // =====================================================
    //  *** EDITA ESTOS VALORES CON TUS CREDENCIALES ***
    // =====================================================
    $smtp_host     = 'smtp.gmail.com';          // Servidor SMTP
    $smtp_usuario  = 'santiagolizcanosuarez@gmail.com';       // Tu correo remitente
    $smtp_password = 'ueud mnzg asuj kvxm';     // Contraseña de aplicación
    $smtp_puerto   = 587;                        // 587 (TLS) o 465 (SSL)
    $smtp_nombre   = 'Sistema PQRS';             // Nombre visible al destinatario
    // =====================================================

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_usuario;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_puerto;
        $mail->CharSet    = 'UTF-8';

        // ⚡ TIMEOUTS CORTOS para evitar que se quede cargando
        $mail->Timeout       = 10;  // Timeout de conexión (segundos)
        $mail->SMTPKeepAlive = false;
        $mail->SMTPOptions   = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom($smtp_usuario, $smtp_nombre);
        $mail->addAddress($para, $nombre ?: 'Usuario');

        $mail->Subject = "Confirmación de Radicación PQRS - $codigo_radicado";
        $mail->isHTML(true);

        $mail->Body = "
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
                .cbox .cod{font-size:26px;font-weight:700;font-family:'Courier New',monospace;margin:10px 0;letter-spacing:2px}
                .det{background:#fff;padding:15px 20px;border-radius:8px;margin:10px 0}
                .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;font-size:14px}
                .row:last-child{border-bottom:none}
                .row span:first-child{color:#6b7280}
                .row span:last-child{font-weight:600}
                .btn{display:inline-block;background:#1e40af;color:#fff!important;padding:12px 32px;text-decoration:none;border-radius:6px;margin-top:20px;font-size:14px;font-weight:600}
                .foot{text-align:center;padding:20px 0 0;color:#9ca3af;font-size:11px}
            </style>
        </head>
        <body>
        <div class='wrap'>
            <div class='head'>
                <div style='font-size:40px;margin-bottom:10px'>✓</div>
                <h1>Solicitud Radicada Exitosamente</h1>
                <p style='margin:5px 0 0;opacity:.85;font-size:14px'>Sistema PQRS — Peticiones, Quejas, Reclamos y Sugerencias</p>
            </div>
            <div class='body'>
                <p>Hola <strong>" . htmlspecialchars($nombre ?: 'Usuario') . "</strong>,</p>
                <p>Su solicitud ha sido registrada correctamente. A continuación los detalles:</p>
                <div class='cbox'>
                    <div class='lbl'>Código de Radicado</div>
                    <div class='cod'>" . htmlspecialchars($codigo_radicado) . "</div>
                    <div style='font-size:13px;opacity:.85'>Guárdelo para consultar el estado</div>
                </div>
                <div class='det'>
                    <div class='row'><span>Tipo de solicitud:</span><span>" . ucfirst(htmlspecialchars($tipo_pqrs)) . "</span></div>
                    <div class='row'><span>Asunto:</span><span>" . htmlspecialchars($asunto_solicitud) . "</span></div>
                    <div class='row'><span>Fecha de radicación:</span><span>" . date('d/m/Y H:i:s') . "</span></div>
                    <div class='row'><span>Fecha límite de respuesta:</span><span>" . htmlspecialchars($fecha_vencimiento) . "</span></div>
                    <div class='row'><span>Estado actual:</span><span style='color:#059669'>● Pendiente</span></div>
                </div>
                <p style='text-align:center'>
                    <a href='http://{$host}/pqrs/consulta_pqrs.php?codigo=" . urlencode($codigo_radicado) . "' class='btn'>
                        Consultar Estado de mi Solicitud
                    </a>
                </p>
                <p style='font-size:12px;color:#9ca3af;margin-top:24px'>
                    Mensaje automático — por favor no responda a este correo.
                </p>
            </div>
            <div class='foot'>
                <p>© " . date('Y') . " Sistema PQRS — Todos los derechos reservados</p>
                <p>Ley 1755 de 2015 · Ley 1437 de 2011</p>
            </div>
        </div>
        </body>
        </html>";

        $mail->AltBody =
            "Solicitud PQRS radicada.\n\n"
            . "Código: $codigo_radicado\n"
            . "Tipo: " . ucfirst($tipo_pqrs) . "\n"
            . "Asunto: $asunto_solicitud\n"
            . "Radicado: " . date('d/m/Y H:i:s') . "\n"
            . "Vencimiento: $fecha_vencimiento\n\n"
            . "Consulte en: http://{$host}/pqrs/consultar.php?codigo=" . urlencode($codigo_radicado);

        $mail->send();
        return true;

    } catch (Exception $e) {
        $logFile = __DIR__ . '/../logs/email_log.txt';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " | FALLO | Para: $para | Codigo: $codigo_radicado | Error: {$mail->ErrorInfo}\n",
            FILE_APPEND | LOCK_EX
        );
        return false;
    }
}
// ──────────────────────────────────────────────────────────────────────────────


// ===== PROCESAR POST =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $con = conexion();
    if (!$con) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    // 1. RECOGER DATOS
    $tipo_pqrs    = mysqli_real_escape_string($con, $_POST['tipo_pqrs']    ?? 'peticion');
    $tipo_persona = mysqli_real_escape_string($con, $_POST['tipo_persona'] ?? 'natural');
    $asunto       = mysqli_real_escape_string($con, $_POST['asunto']       ?? '');
    $descripcion  = mysqli_real_escape_string($con, $_POST['descripcion']  ?? '');
    $notificar    = isset($_POST['notificar_correo']) ? 1 : 0;

    $nombre_completo      = null;
    $documento_identidad  = null;
    $tipo_documento       = null;
    $correo_electronico   = null;
    $telefono             = null;
    $razon_social         = null;
    $nit                  = null;
    $nombre_representante = null;
    $correo_corporativo   = null;

    if ($tipo_persona === 'natural') {
        $nombre_completo     = mysqli_real_escape_string($con, $_POST['nombre']            ?? '');
        $documento_identidad = mysqli_real_escape_string($con, $_POST['numero_documento']  ?? '');
        $tipo_documento      = mysqli_real_escape_string($con, $_POST['tipo_documento']    ?? '');
        $correo_electronico  = mysqli_real_escape_string($con, $_POST['correo']            ?? '');
        $telefono            = mysqli_real_escape_string($con, $_POST['telefono']          ?? '');
    } elseif ($tipo_persona === 'juridica') {
        $razon_social         = mysqli_real_escape_string($con, $_POST['razon_social']     ?? '');
        $nit                  = mysqli_real_escape_string($con, $_POST['nit']              ?? '');
        $nombre_representante = mysqli_real_escape_string($con, $_POST['representante']    ?? '');
        $correo_corporativo   = mysqli_real_escape_string($con, $_POST['correo_corporativo'] ?? '');
        $telefono             = mysqli_real_escape_string($con, $_POST['telefono_juridica'] ?? '');
        $correo_electronico   = $correo_corporativo;
        $nombre_completo      = $nombre_representante;
    }

    // 2. INSERTAR USUARIO
    $sqlUsuario = "INSERT INTO usuario (
        tipo_persona, nombre_completo, documento_identidad, tipo_documento,
        correo_electronico, telefono, razon_social, nit,
        nombre_representante, correo_corporativo
    ) VALUES (
        '$tipo_persona',
        " . ($nombre_completo      ? "'$nombre_completo'"      : "NULL") . ",
        " . ($documento_identidad  ? "'$documento_identidad'"  : "NULL") . ",
        " . ($tipo_documento       ? "'$tipo_documento'"       : "NULL") . ",
        " . ($correo_electronico   ? "'$correo_electronico'"   : "NULL") . ",
        " . ($telefono             ? "'$telefono'"             : "NULL") . ",
        " . ($razon_social         ? "'$razon_social'"         : "NULL") . ",
        " . ($nit                  ? "'$nit'"                  : "NULL") . ",
        " . ($nombre_representante ? "'$nombre_representante'" : "NULL") . ",
        " . ($correo_corporativo   ? "'$correo_corporativo'"   : "NULL") . "
    )";

    if (!mysqli_query($con, $sqlUsuario)) {
        die("Error al insertar usuario: " . mysqli_error($con));
    }
    $usuario_id = mysqli_insert_id($con);

    // 3. GENERAR CÓDIGO RADICADO
    $anio = date('Y');
    $mes  = date('m');

    $sqlConsecutivo = "SELECT COUNT(*) as total FROM pqrs
                       WHERE YEAR(fecha_radicacion) = $anio
                       AND MONTH(fecha_radicacion)  = $mes";
    $resultCons  = mysqli_query($con, $sqlConsecutivo);
    $rowCons     = mysqli_fetch_assoc($resultCons);
    $consecutivo = str_pad(($rowCons['total'] + 1), 3, '0', STR_PAD_LEFT);

    $codigo_radicado = "PQRS-{$anio}-{$mes}-{$consecutivo}";

    // 4. CALCULAR FECHA DE VENCIMIENTO
    $sqlConfig  = "SELECT dias_vencimiento_peticion, dias_vencimiento_queja,
                   dias_vencimiento_reclamo, dias_vencimiento_sugerencia,
                   dias_vencimiento_denuncia
                   FROM configuracion_sistema WHERE id = 1";
    $resultConfig    = mysqli_query($con, $sqlConfig);
    $config          = mysqli_fetch_assoc($resultConfig);
    $campoDias       = 'dias_vencimiento_' . $tipo_pqrs;
    $dias_vencimiento = isset($config[$campoDias]) ? (int)$config[$campoDias] : 15;
    $fecha_vencimiento = date('Y-m-d', strtotime("+$dias_vencimiento days"));

    // 5. MANEJAR ARCHIVO ADJUNTO
    $archivo_adjunto = null;
    if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = time() . '_' . basename($_FILES['adjunto']['name']);
        $rutaDestino   = 'uploads/' . $nombreArchivo;
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }
        if (move_uploaded_file($_FILES['adjunto']['tmp_name'], $rutaDestino)) {
            $archivo_adjunto = mysqli_real_escape_string($con, $rutaDestino);
        }
    }

    // 6. INSERTAR PQRS
    $sqlPQRS = "INSERT INTO pqrs (
        codigo_radicado, tipo_solicitud, asunto, descripcion,
        archivo_adjunto, estado, fecha_vencimiento,
        usuario_id, administrador_id
    ) VALUES (
        '$codigo_radicado', '$tipo_pqrs', '$asunto', '$descripcion',
        " . ($archivo_adjunto ? "'$archivo_adjunto'" : "NULL") . ",
        'PENDIENTE', '$fecha_vencimiento',
        $usuario_id, NULL
    )";

    if (!mysqli_query($con, $sqlPQRS)) {
        die("Error al insertar PQRS: " . mysqli_error($con));
    }
    $pqrs_id = mysqli_insert_id($con);

    // 7. ENVIAR CORREO CON PHPMAILER
    $correoDestino = null;
    if ($tipo_persona === 'natural') {
        $correoDestino = $correo_electronico;
    } elseif ($tipo_persona === 'juridica') {
        $correoDestino = $correo_corporativo;
    }

    $correoEnviado = false;

    if ($notificar && !empty($correoDestino)) {
        $correoEnviado = enviarCorreoPQRS(
            $correoDestino,
            $nombre_completo ?? '',
            $codigo_radicado,
            $tipo_pqrs,
            $asunto,
            date('d/m/Y', strtotime($fecha_vencimiento)),
            $_SERVER['HTTP_HOST']
        );

        // Log de éxito también
        $logFile = __DIR__ . '/../logs/email_log.txt';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . " | " . ($correoEnviado ? "ENVIADO" : "FALLO") . " | Para: $correoDestino | Codigo: $codigo_radicado\n",
            FILE_APPEND | LOCK_EX
        );
    }

    mysqli_close($con);

    // 8. REDIRIGIR A CONFIRMACIÓN
    session_start();
    $_SESSION['correo_enviado'] = $correoEnviado;

    header("Location: confirmacion.php?id=$pqrs_id");
    exit();
}

// ===== GET: mostrar formulario =====
$tipoPQRS    = isset($_GET['tipo_pqrs']) ? htmlspecialchars($_GET['tipo_pqrs']) : 'peticion';
$nombresTipos = [
    'peticion'  => 'Petición',
    'queja'     => 'Queja',
    'reclamo'   => 'Reclamo',
    'sugerencia'=> 'Sugerencia',
    'denuncia'  => 'Denuncia'
];
$nombreTipo = $nombresTipos[$tipoPQRS] ?? 'Petición';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario PQRS - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="formulario-page">

    <header style="background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:1rem 0;margin-bottom:2rem;">
        <div style="max-width:800px;margin:0 auto;padding:0 1rem;display:flex;align-items:center;gap:0.5rem;">
            <i class="bi bi-clipboard-data" style="color:#1e40af;font-size:1.5rem;"></i>
            <span style="font-weight:700;color:#1e40af;font-size:1.25rem;">Sistema PQRS</span>
        </div>
    </header>

    <div class="formulario-container">
        <div class="formulario-card">

            <div class="formulario-header">
                <div class="formulario-tipo-badge">
                    <i class="bi bi-file-text"></i>
                    <span><?php echo $nombreTipo; ?></span>
                </div>
                <h1 class="formulario-titulo">Complete su solicitud</h1>
            </div>

            <div class="persona-selector">
                <button type="button" class="persona-btn activo" onclick="cambiarPersona('natural')" data-persona="natural">
                    <i class="bi bi-person"></i>
                    <span>Persona Natural</span>
                </button>
                <button type="button" class="persona-btn" onclick="cambiarPersona('juridica')" data-persona="juridica">
                    <i class="bi bi-building"></i>
                    <span>Persona Jurídica</span>
                </button>
                <button type="button" class="persona-btn" onclick="cambiarPersona('anonima')" data-persona="anonima">
                    <i class="bi bi-incognito"></i>
                    <span>Anónima</span>
                </button>
            </div>

            <form id="formPQRS" action="formulario.php" method="POST" enctype="multipart/form-data" onsubmit="return validarFormulario()">

                <input type="hidden" name="tipo_pqrs" value="<?php echo $tipoPQRS; ?>">
                <input type="hidden" id="tipo_persona" name="tipo_persona" value="natural">

                <!-- PERSONA NATURAL -->
                <div id="seccion-natural" class="form-body">
                    <div class="form-row">
                        <div class="form-grupo">
                            <label class="form-label">Nombre completo <span class="requerido">*</span></label>
                            <input type="text" name="nombre" class="form-input" placeholder="Ej: Juan Pérez García" onblur="validarCampo(this, 'nombre')">
                            <span class="error-mensaje" id="error-nombre">Ingrese su nombre completo</span>
                        </div>
                        <div class="form-grupo">
                            <label class="form-label">Tipo de documento <span class="requerido">*</span></label>
                            <select name="tipo_documento" class="form-select" onblur="validarCampo(this, 'tipo_documento')">
                                <option value="">Seleccione...</option>
                                <option value="CC">Cédula de Ciudadanía</option>
                                <option value="CE">Cédula de Extranjería</option>
                                <option value="TI">Tarjeta de Identidad</option>
                                <option value="PAS">Pasaporte</option>
                            </select>
                            <span class="error-mensaje" id="error-tipo_documento">Seleccione un tipo de documento</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-grupo">
                            <label class="form-label">Número de documento <span class="requerido">*</span></label>
                            <input type="text" name="numero_documento" class="form-input" placeholder="Ej: 1234567890" onblur="validarCampo(this, 'numero_documento')">
                            <span class="error-mensaje" id="error-numero_documento">Ingrese su número de documento</span>
                        </div>
                        <div class="form-grupo">
                            <label class="form-label">Teléfono <span class="requerido">*</span></label>
                            <input type="tel" name="telefono" class="form-input" placeholder="Ej: 3001234567" onblur="validarCampo(this, 'telefono')">
                            <span class="error-mensaje" id="error-telefono">Ingrese un número de teléfono válido</span>
                        </div>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Correo electrónico <span class="requerido">*</span></label>
                        <input type="email" name="correo" class="form-input" placeholder="Ej: correo@ejemplo.com" onblur="validarCampo(this, 'correo')">
                        <span class="error-mensaje" id="error-correo">Ingrese un correo electrónico válido</span>
                    </div>
                </div>

                <!-- PERSONA JURÍDICA -->
                <div id="seccion-juridica" class="form-body oculto">
                    <div class="form-row">
                        <div class="form-grupo">
                            <label class="form-label">Razón social <span class="requerido">*</span></label>
                            <input type="text" name="razon_social" class="form-input" placeholder="Ej: Empresa S.A.S." onblur="validarCampo(this, 'razon_social')">
                            <span class="error-mensaje" id="error-razon_social">Ingrese la razón social</span>
                        </div>
                        <div class="form-grupo">
                            <label class="form-label">NIT <span class="requerido">*</span></label>
                            <input type="text" name="nit" class="form-input" placeholder="Ej: 900123456-7" onblur="validarCampo(this, 'nit')">
                            <span class="error-mensaje" id="error-nit">Ingrese el NIT</span>
                        </div>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Nombre del representante legal <span class="requerido">*</span></label>
                        <input type="text" name="representante" class="form-input" placeholder="Ej: Carlos Rodríguez" onblur="validarCampo(this, 'representante')">
                        <span class="error-mensaje" id="error-representante">Ingrese el nombre del representante</span>
                    </div>
                    <div class="form-row">
                        <div class="form-grupo">
                            <label class="form-label">Correo corporativo <span class="requerido">*</span></label>
                            <input type="email" name="correo_corporativo" class="form-input" placeholder="Ej: contacto@empresa.com" onblur="validarCampo(this, 'correo_corporativo')">
                            <span class="error-mensaje" id="error-correo_corporativo">Ingrese un correo corporativo válido</span>
                        </div>
                        <div class="form-grupo">
                            <label class="form-label">Teléfono <span class="requerido">*</span></label>
                            <input type="tel" name="telefono_juridica" class="form-input" placeholder="Ej: 6011234567" onblur="validarCampo(this, 'telefono_juridica')">
                            <span class="error-mensaje" id="error-telefono_juridica">Ingrese un número de teléfono válido</span>
                        </div>
                    </div>
                </div>

                <!-- ANÓNIMA -->
                <div id="seccion-anonima" class="form-body oculto">
                    <div style="padding:1rem;background:#fef3c7;border-radius:0.5rem;margin-bottom:1rem;">
                        <p style="margin:0;color:#92400e;font-size:0.875rem;">
                            <i class="bi bi-info-circle" style="margin-right:0.25rem;"></i>
                            Al ser anónima, no se requieren datos personales. Solo se necesita el asunto y descripción de su solicitud.
                        </p>
                    </div>
                </div>

                <!-- CAMPOS COMUNES -->
                <div class="form-body" style="border-top:1px solid #e5e7eb;">
                    <div class="form-grupo">
                        <label class="form-label">Asunto <span class="requerido">*</span></label>
                        <input type="text" name="asunto" class="form-input" placeholder="Resumen breve de su solicitud" onblur="validarCampo(this, 'asunto')">
                        <span class="error-mensaje" id="error-asunto">Ingrese el asunto de su solicitud</span>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Descripción detallada <span class="requerido">*</span></label>
                        <textarea name="descripcion" class="form-textarea" placeholder="Describa detalladamente su solicitud..." onblur="validarCampo(this, 'descripcion')"></textarea>
                        <span class="error-mensaje" id="error-descripcion">Ingrese la descripción de su solicitud</span>
                    </div>

                    <div id="notificacion-correo" class="form-grupo">
                        <label class="checkbox-container" style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;padding:0.75rem;background:#f0fdf4;border-radius:0.5rem;border:1px solid #bbf7d0;">
                            <input type="checkbox" name="notificar_correo" value="1" checked style="width:18px;height:18px;accent-color:#059669;">
                            <span style="font-size:0.875rem;color:#065f46;">
                                <i class="bi bi-envelope-check" style="margin-right:0.25rem;color:#059669;"></i>
                                <strong>Deseo recibir notificación por correo electrónico</strong> con el código de radicado y estado de mi solicitud
                            </span>
                        </label>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Archivo adjunto (opcional)</label>
                        <div class="form-adjunto">
                            <input type="file" id="adjunto" name="adjunto" class="form-adjunto-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" onchange="mostrarArchivo(this)">
                            <label for="adjunto" class="form-adjunto-label">
                                <i class="bi bi-paperclip"></i>
                                <span id="nombre-archivo">Haga clic para adjuntar archivo (PDF, Word, JPG, PNG)</span>
                            </label>
                        </div>
                        <p class="form-ayuda">Tamaño máximo: 5MB</p>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="tipos.php" class="btn-volver">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                    <button type="submit" class="btn-enviar">
                        <i class="bi bi-send"></i>
                        Enviar Solicitud
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
    function cambiarPersona(tipo) {
        document.querySelectorAll('.persona-btn').forEach(btn => btn.classList.remove('activo'));
        document.querySelector(`.persona-btn[data-persona="${tipo}"]`).classList.add('activo');
        ['natural','juridica','anonima'].forEach(t => {
            document.getElementById(`seccion-${t}`).classList.add('oculto');
        });
        document.getElementById(`seccion-${tipo}`).classList.remove('oculto');
        document.getElementById('tipo_persona').value = tipo;
        document.querySelectorAll('.error-mensaje').forEach(e => e.classList.remove('visible'));
        document.querySelectorAll('.form-input,.form-select,.form-textarea').forEach(i => i.classList.remove('error'));
    }

    function mostrarArchivo(input) {
        if (input.files && input.files[0]) {
            document.getElementById('nombre-archivo').textContent = input.files[0].name;
        }
    }

    function validarCampo(input, campo) {
        const valor = input.value.trim();
        const errorMsg = document.getElementById(`error-${campo}`);
        let valido = !!valor;
        if (campo.includes('correo') && valor && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) valido = false;
        if (campo.includes('telefono') && valor && !/^[0-9]{7,15}$/.test(valor.replace(/\s/g,''))) valido = false;
        input.classList.toggle('error', !valido);
        if (errorMsg) errorMsg.classList.toggle('visible', !valido);
        return valido;
    }

    function validarFormulario() {
        const tipoPersona = document.getElementById('tipo_persona').value;
        let campos = tipoPersona === 'natural'
            ? ['nombre','tipo_documento','numero_documento','telefono','correo']
            : tipoPersona === 'juridica'
            ? ['razon_social','nit','representante','correo_corporativo','telefono_juridica']
            : [];
        campos = campos.concat(['asunto','descripcion']);
        const valido = campos.every(campo => {
            const input = document.querySelector(`[name="${campo}"]`);
            return input ? validarCampo(input, campo) : true;
        });
        if (!valido) alert('Por favor complete todos los campos obligatorios marcados.');
        return valido;
    }
    </script>

</body>
</html>