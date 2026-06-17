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

    private function logEmail(string $mensaje): void {
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

    private function enviarCorreoPQRS(
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
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $cfg['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['smtp_user'];
            $mail->Password   = $cfg['smtp_password'];
            $mail->SMTPSecure = $cfg['smtp_encryption'] === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $cfg['smtp_port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            $mail->addAddress($para, $nombre ?: 'Usuario');
            $mail->Subject = "Confirmacion de Radicacion PQRS - $codigo_radicado";

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
                    <div style='font-size:40px;margin-bottom:10px'>&#10003;</div>
                    <h1>Solicitud Radicada Exitosamente</h1>
                    <p style='margin:5px 0 0;opacity:.85;font-size:14px'>Sistema PQRS</p>
                </div>
                <div class='body'>
                    <p>Hola <strong>" . htmlspecialchars($nombre ?: 'Usuario') . "</strong>,</p>
                    <p>Su solicitud ha sido registrada correctamente.</p>
                    <div class='cbox'>
                        <div class='lbl'>Codigo de Radicado</div>
                        <div class='cod'>" . htmlspecialchars($codigo_radicado) . "</div>
                        <div style='font-size:13px;opacity:.85'>Guardelo para consultar el estado</div>
                    </div>
                    <div class='det'>
                        <div class='row'><span>Tipo de solicitud:</span><span>" . ucfirst(htmlspecialchars($tipo_pqrs)) . "</span></div>
                        <div class='row'><span>Asunto:</span><span>" . htmlspecialchars($asunto_solicitud) . "</span></div>
                        <div class='row'><span>Fecha de radicacion:</span><span>" . date('d/m/Y H:i:s') . "</span></div>
                        <div class='row'><span>Fecha limite de respuesta:</span><span>" . htmlspecialchars($fecha_vencimiento) . "</span></div>
                        <div class='row'><span>Estado actual:</span><span style='color:#059669'>Pendiente</span></div>
                    </div>
                    <p style='text-align:center'>
                        <a href='http://{$host}" . BASE_PATH . "index.php?ruta=pqrs/consulta&codigo=" . urlencode($codigo_radicado) . "' class='btn'>
                            Consultar Estado de mi Solicitud
                        </a>
                    </p>
                    <p style='font-size:12px;color:#9ca3af;margin-top:24px'>Mensaje automatico, no responda este correo.</p>
                </div>
                <div class='foot'>
                    <p>&copy; " . date('Y') . " Sistema PQRS</p>
                    <p>Ley 1755 de 2015 &middot; Ley 1437 de 2011</p>
                </div>
            </div>
            </body>
            </html>";

            $mail->AltBody = "Solicitud PQRS radicada.\n\n"
                . "Codigo: $codigo_radicado\n"
                . "Tipo: " . ucfirst($tipo_pqrs) . "\n"
                . "Asunto: $asunto_solicitud\n"
                . "Radicado: " . date('d/m/Y H:i:s') . "\n"
                . "Vencimiento: $fecha_vencimiento\n\n"
                . "Consulte en: http://{$host}" . BASE_PATH . "index.php?ruta=pqrs/consulta&codigo=" . urlencode($codigo_radicado);

            $mail->send();
            return true;

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $this->logEmail(date('Y-m-d H:i:s') . " | FALLO-SMTP | Para: $para | Error: " . $e->getMessage() . "\n");
            return false;
        }
    }

    public function radicar() {
        // ⚡ SESSION START PRIMERO — antes de cualquier output
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../../config/conexion.php';
        require_once __DIR__ . '/../../vendor/autoload.php';

        // ===== PROCESAR POST =====
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $con = conexion();
            if (!$con) {
                die("Error de conexión: " . mysqli_connect_error());
            }

            // 1. RECOGER Y SANITIZAR DATOS BÁSICOS
            $tipos_pqrs_validos    = ['peticion', 'queja', 'reclamo', 'sugerencia', 'denuncia'];
            $tipos_persona_validos = ['natural', 'juridica', 'anonima'];

            $tipo_pqrs    = in_array($_POST['tipo_pqrs']    ?? '', $tipos_pqrs_validos)    ? $_POST['tipo_pqrs']    : 'peticion';
            $tipo_persona = in_array($_POST['tipo_persona'] ?? '', $tipos_persona_validos) ? $_POST['tipo_persona'] : 'natural';
            $asunto       = mb_substr(trim($_POST['asunto']      ?? ''), 0, 250);
            $descripcion  = mb_substr(trim($_POST['descripcion'] ?? ''), 0, 5000);
            $notificar    = ($tipo_persona !== 'anonima' && isset($_POST['notificar_correo'])) ? 1 : 0;

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
                $nombre_completo     = trim($_POST['nombre']            ?? '') ?: null;
                $documento_identidad = trim($_POST['numero_documento']  ?? '') ?: null;
                $tipo_documento      = trim($_POST['tipo_documento']    ?? '') ?: null;
                $correo_electronico  = trim($_POST['correo']            ?? '') ?: null;
                $telefono            = trim($_POST['telefono']          ?? '') ?: null;
            } elseif ($tipo_persona === 'juridica') {
                $razon_social         = trim($_POST['razon_social']        ?? '') ?: null;
                $nit                  = trim($_POST['nit']                 ?? '') ?: null;
                $nombre_representante = trim($_POST['representante']       ?? '') ?: null;
                $correo_corporativo   = trim($_POST['correo_corporativo']  ?? '') ?: null;
                $telefono             = trim($_POST['telefono_juridica']   ?? '') ?: null;
                $correo_electronico   = $correo_corporativo;
                $nombre_completo      = $nombre_representante;
            }

            // 2. INSERTAR USUARIO
            $stmtUsuario = mysqli_prepare($con,
                "INSERT INTO usuario (
                    tipo_persona, nombre_completo, documento_identidad, tipo_documento,
                    correo_electronico, telefono, razon_social, nit,
                    nombre_representante, correo_corporativo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$stmtUsuario) {
                die("Error preparando sentencia usuario: " . mysqli_error($con));
            }
            mysqli_stmt_bind_param($stmtUsuario, 'ssssssssss',
                $tipo_persona, $nombre_completo, $documento_identidad, $tipo_documento,
                $correo_electronico, $telefono, $razon_social, $nit,
                $nombre_representante, $correo_corporativo
            );
            if (!mysqli_stmt_execute($stmtUsuario)) {
                die("Error al insertar usuario: " . mysqli_stmt_error($stmtUsuario));
            }
            $usuario_id = mysqli_stmt_insert_id($stmtUsuario);
            mysqli_stmt_close($stmtUsuario);

            // 3. GENERAR CÓDIGO RADICADO
            $anio = date('Y');
            $mes  = date('m');
            $sqlConsecutivo = "SELECT MAX(CAST(SUBSTRING(codigo_radicado, -3) AS UNSIGNED)) as max_num
                            FROM pqrs
                            WHERE YEAR(fecha_radicacion) = $anio
                            AND MONTH(fecha_radicacion) = $mes";
            $resultCons  = mysqli_query($con, $sqlConsecutivo);
            $rowCons     = mysqli_fetch_assoc($resultCons);
            $maxNum      = $rowCons['max_num'] ?? 0;
            $consecutivo = str_pad(($maxNum + 1), 3, '0', STR_PAD_LEFT);
            $codigo_radicado = "PQRS-{$anio}-{$mes}-{$consecutivo}";

            // 4. CALCULAR FECHA DE VENCIMIENTO
            $sqlConfig = "SELECT dias_vencimiento_peticion, dias_vencimiento_queja,
                        dias_vencimiento_reclamo, dias_vencimiento_sugerencia,
                        dias_vencimiento_denuncia
                        FROM configuracion_sistema WHERE id = 1";
            $resultConfig     = mysqli_query($con, $sqlConfig);
            $config           = mysqli_fetch_assoc($resultConfig);
            $campoDias        = 'dias_vencimiento_' . $tipo_pqrs;
            $dias_vencimiento = isset($config[$campoDias]) ? (int)$config[$campoDias] : 15;
            $fecha_vencimiento = date('Y-m-d', strtotime("+$dias_vencimiento days"));

            // 5. MANEJAR ARCHIVO ADJUNTO
            $archivo_adjunto = null;
            if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
                $nombreArchivo = time() . '_' . basename($_FILES['adjunto']['name']);
                $dirDestino  = dirname(__DIR__, 2) . '/uploads';
                $rutaDestino = $dirDestino . '/' . $nombreArchivo;

                if (!is_dir($dirDestino)) {
                    @mkdir($dirDestino, 0755, true);
                }
                if (@move_uploaded_file($_FILES['adjunto']['tmp_name'], $rutaDestino)) {
                    $archivo_adjunto = $nombreArchivo;
                }
            }

            // 6. INSERTAR PQRS
            $estado_inicial = 'PENDIENTE';
            $admin_id_null  = null;
            $stmtPQRS = mysqli_prepare($con,
                "INSERT INTO pqrs (
                    codigo_radicado, tipo_solicitud, asunto, descripcion,
                    archivo_adjunto, estado, fecha_vencimiento,
                    desea_notificacion, usuario_id, administrador_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$stmtPQRS) {
                die("Error preparando sentencia pqrs: " . mysqli_error($con));
            }
            mysqli_stmt_bind_param($stmtPQRS, 'sssssssiis',
                $codigo_radicado, $tipo_pqrs, $asunto, $descripcion,
                $archivo_adjunto, $estado_inicial, $fecha_vencimiento,
                $notificar, $usuario_id, $admin_id_null
            );
            if (!mysqli_stmt_execute($stmtPQRS)) {
                die("Error al insertar PQRS: " . mysqli_stmt_error($stmtPQRS));
            }
            $pqrs_id = mysqli_stmt_insert_id($stmtPQRS);
            mysqli_stmt_close($stmtPQRS);

            // 7. ENVIAR CORREO
            $correoDestino = null;
            if ($tipo_persona === 'natural') {
                $correoDestino = $correo_electronico;
            } elseif ($tipo_persona === 'juridica') {
                $correoDestino = $correo_corporativo;
            }

            $correoEnviado = false;
            if ($notificar && !empty($correoDestino)) {
                $correoEnviado = $this->enviarCorreoPQRS(
                    $correoDestino,
                    $nombre_completo ?? '',
                    $codigo_radicado,
                    $tipo_pqrs,
                    $asunto,
                    date('d/m/Y', strtotime($fecha_vencimiento)),
                    $_SERVER['HTTP_HOST']
                );
                $this->logEmail(date('Y-m-d H:i:s') . " | " . ($correoEnviado ? "ENVIADO" : "FALLO") . " | Para: $correoDestino | Codigo: $codigo_radicado\n");
            }

            mysqli_close($con);

            $_SESSION['correo_enviado'] = $correoEnviado;

            header("Location: " . BASE_PATH . "index.php?ruta=pqrs/confirmacion&id=$pqrs_id");
            exit();
        }
    }

    public function confirmacion() {
        $id = $_GET['id'] ?? '';
        require_once __DIR__ . '/../views/pqrs/confirmacion.php';
    }

    public function consulta() {
        require_once __DIR__ . '/../views/pqrs/consulta.php';
    }
    
    public function tipos() {
        require_once __DIR__ . '/../views/pqrs/tipos.php';
    }
}
