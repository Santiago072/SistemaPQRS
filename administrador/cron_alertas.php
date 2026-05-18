<?php
/**
 * Sistema de Alertas Automáticas por Email
 * Envía notificaciones de PQRS próximas a vencer o vencidas
 * al correo configurado en la tabla configuracion_sistema
 * 
 * HU: Como administrador, quiero recibir notificaciones automáticas 
 * cuando una o varias PQRS se acerque al vencimiento legal, 
 * para atenderla a tiempo y evitar sanciones.
 * 
 * Criterios de Aceptación:
 * - Alertas a 5, 10 y 15 días según tipo de solicitud
 * - Recibir alertas al correo electrónico del admin
 * - Indicadores visuales en la bandeja (colores por urgencia)
 * - Notificación visual en el panel
 */

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/email_sendgrid.php';
require_once __DIR__ . '/../includes/funciones.php';

/**
 * Envía alertas de PQRS vencidas o próximas a vencer
 * Según HU: alertas a 5, 10 y 15 días según tipo de solicitud
 */
function enviarAlertasEmail() {
    $con = conexion();
    if (!$con) {
        error_log("Error: No se pudo conectar a la base de datos");
        return false;
    }

    // Obtener correo de notificaciones desde configuración
    $correo_destino = obtenerCorreoNotificaciones();
    
    if (empty($correo_destino)) {
        error_log("Error: No hay correo de notificaciones configurado");
        mysqli_close($con);
        return false;
    }

    // Obtener configuración de días de vencimiento por tipo
    $config_query = "SELECT * FROM configuracion_sistema WHERE id = 1";
    $config_result = mysqli_query($con, $config_query);
    $config = mysqli_fetch_assoc($config_result);

    // Obtener PQRS próximas a vencer o vencidas
    // Alertas a 5, 10 y 15 días según tipo de solicitud
    $query = "SELECT p.*, u.nombre_completo, u.correo_electronico,
                     DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
              FROM pqrs p
              LEFT JOIN usuario u ON p.usuario_id = u.id
              WHERE p.estado NOT IN ('RESUELTO', 'RECHAZADO')
                AND p.fecha_vencimiento IS NOT NULL
                AND (
                    DATEDIFF(p.fecha_vencimiento, CURDATE()) <= 15
                    OR DATEDIFF(p.fecha_vencimiento, CURDATE()) < 0
                )
              ORDER BY dias_restantes ASC";

    $result = mysqli_query($con, $query);
    if (!$result || mysqli_num_rows($result) === 0) {
        mysqli_close($con);
        return true; // No hay alertas, no es error
    }

    $pqrs_alerta = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pqrs_alerta[] = $row;
        
        // Guardar alerta en tabla alerta_vencimiento para tracking
        $dias = intval($row['dias_restantes']);
        $nivel = determinarNivelAlerta($dias);
        guardarAlerta($row['id'], $dias, $nivel);
    }
    mysqli_close($con);

    // Construir el cuerpo del email con indicadores visuales (colores por urgencia)
    $html = construirEmailAlertas($pqrs_alerta);

    // Enviar email vía SendGrid al correo del admin
    $enviado = enviarEmailSendGrid(
        $correo_destino, 
        '🚨 Alerta PQRS - Solicitudes Próximas a Vencer', 
        $html
    );

    if ($enviado) {
        error_log("Alertas enviadas exitosamente a: {$correo_destino}");
        marcarAlertasEnviadas($pqrs_alerta);
    } else {
        error_log("Error al enviar alertas vía SendGrid");
    }

    return $enviado;
}

/**
 * Determina el nivel de alerta según días restantes
 * HU: Alertas a 5, 10 y 15 días según tipo de solicitud
 */
function determinarNivelAlerta($dias) {
    if ($dias < 0) return 'VENCIDA';      // Rojo - Vencida
    if ($dias <= 5) return 'CRITICO';      // Rojo - Crítico (0-5 días)
    if ($dias <= 10) return 'URGENTE';     // Naranja - Urgente (6-10 días)
    if ($dias <= 15) return 'MODERADO';    // Amarillo - Moderado (11-15 días)
    return 'NORMAL';
}

/**
 * Obtiene el color CSS según el nivel de alerta
 */
function getColorAlerta($nivel) {
    $colores = [
        'VENCIDA'  => '#dc2626',
        'CRITICO'  => '#dc2626',
        'URGENTE'  => '#ea580c',
        'MODERADO' => '#ca8a04',
        'NORMAL'   => '#16a34a'
    ];
    return $colores[$nivel] ?? '#6b7280';
}

/**
 * Obtiene el emoji/icono según el nivel de alerta
 */
function getIconoAlerta($nivel) {
    $iconos = [
        'VENCIDA'  => '🔴',
        'CRITICO'  => '🚨',
        'URGENTE'  => '⚠️',
        'MODERADO' => '⏰',
        'NORMAL'   => '✅'
    ];
    return $iconos[$nivel] ?? 'ℹ️';
}

/**
 * Construye el HTML del email de alertas
 * Con indicadores visuales (colores por urgencia)
 */
function construirEmailAlertas($pqrs_list) {
    $fecha_actual = date('d/m/Y H:i:s');
    
    // Contar por categorías
    $vencidas = array_filter($pqrs_list, fn($p) => intval($p['dias_restantes']) < 0);
    $criticas = array_filter($pqrs_list, fn($p) => intval($p['dias_restantes']) >= 0 && intval($p['dias_restantes']) <= 5);
    $urgentes = array_filter($pqrs_list, fn($p) => intval($p['dias_restantes']) >= 6 && intval($p['dias_restantes']) <= 10);
    $moderadas = array_filter($pqrs_list, fn($p) => intval($p['dias_restantes']) >= 11 && intval($p['dias_restantes']) <= 15);

    $html = '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Alertas PQRS - Sistema de Gestión</title>
        <style>
            body { 
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
                background: #f3f4f6; 
                margin: 0; 
                padding: 20px; 
                color: #374151;
            }
            .container { 
                max-width: 700px; 
                margin: 0 auto; 
                background: white; 
                border-radius: 12px; 
                overflow: hidden; 
                box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            }
            .header { 
                background: #1e3a8a; 
                color: white; 
                padding: 30px 20px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 24px; 
                font-weight: 600;
            }
            .header p {
                margin: 10px 0 0 0;
                opacity: 0.9;
                font-size: 14px;
            }
            .resumen {
                display: flex;
                justify-content: space-around;
                padding: 20px;
                background: #f9fafb;
                border-bottom: 1px solid #e5e7eb;
            }
            .resumen-item {
                text-align: center;
            }
            .resumen-num {
                font-size: 28px;
                font-weight: bold;
                display: block;
            }
            .resumen-label {
                font-size: 12px;
                color: #6b7280;
                text-transform: uppercase;
            }
            .content { 
                padding: 25px; 
            }
            .alerta { 
                border-left: 5px solid; 
                padding: 20px; 
                margin-bottom: 20px; 
                background: #f9fafb; 
                border-radius: 0 8px 8px 0;
            }
            .alerta.vencida { 
                border-color: #dc2626; 
                background: #fef2f2; 
            }
            .alerta.critico { 
                border-color: #dc2626; 
                background: #fef2f2; 
            }
            .alerta.urgente { 
                border-color: #ea580c; 
                background: #fff7ed; 
            }
            .alerta.moderado { 
                border-color: #ca8a04; 
                background: #fefce8; 
            }
            .alerta-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }
            .alerta-tipo {
                font-weight: bold;
                font-size: 14px;
                padding: 4px 12px;
                border-radius: 20px;
                color: white;
            }
            .alerta-dias {
                font-size: 24px;
                font-weight: bold;
            }
            .codigo { 
                font-weight: bold; 
                color: #1e3a8a; 
                font-size: 16px; 
                font-family: monospace;
                background: #dbeafe;
                padding: 4px 8px;
                border-radius: 4px;
            }
            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin-top: 12px;
            }
            .info-item {
                font-size: 13px;
            }
            .info-label {
                color: #6b7280;
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .info-value {
                font-weight: 500;
                color: #111827;
            }
            .estado {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 11px;
                font-weight: bold;
                text-transform: uppercase;
            }
            .estado-pendiente { background: #fef3c7; color: #92400e; }
            .estado-proceso { background: #dbeafe; color: #1e40af; }
            .footer { 
                background: #f3f4f6; 
                padding: 20px; 
                text-align: center; 
                font-size: 12px; 
                color: #6b7280; 
            }
            @media (max-width: 600px) {
                .info-grid { grid-template-columns: 1fr; }
                .resumen { flex-direction: column; gap: 15px; }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔔 Alerta de Vencimiento PQRS</h1>
                <p>Sistema de Gestión de Peticiones, Quejas, Reclamos y Sugerencias</p>
            </div>
            <div class="resumen">
                <div class="resumen-item">
                    <span class="resumen-num" style="color: #dc2626;">' . count($vencidas) . '</span>
                    <span class="resumen-label">Vencidas</span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-num" style="color: #dc2626;">' . count($criticas) . '</span>
                    <span class="resumen-label">Críticas (0-5d)</span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-num" style="color: #ea580c;">' . count($urgentes) . '</span>
                    <span class="resumen-label">Urgentes (6-10d)</span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-num" style="color: #ca8a04;">' . count($moderadas) . '</span>
                    <span class="resumen-label">Moderadas (11-15d)</span>
                </div>
            </div>
            <div class="content">
                <p style="margin-bottom: 25px; color: #4b5563;">
                    Se han detectado <strong>' . count($pqrs_list) . '</strong> solicitudes que requieren atención. 
                    A continuación el detalle según nivel de urgencia:
                </p>';

    foreach ($pqrs_list as $pqrs) {
        $dias = intval($pqrs['dias_restantes']);
        $nivel = determinarNivelAlerta($dias);
        $clase = strtolower($nivel);
        $color = getColorAlerta($nivel);
        $icono = getIconoAlerta($nivel);

        if ($dias < 0) {
            $texto_dias = 'VENCIDA hace ' . abs($dias) . ' día' . (abs($dias) != 1 ? 's' : '');
        } else {
            $texto_dias = $dias . ' día' . ($dias != 1 ? 's' : '') . ' restante' . ($dias != 1 ? 's' : '');
        }

        $estadoClass = strtolower(str_replace('_', '-', $pqrs['estado']));
        $estadoLabels = [
            'PENDIENTE' => 'Pendiente',
            'EN_PROCESO' => 'En Proceso',
            'RESUELTO' => 'Resuelto',
            'RECHAZADO' => 'Rechazado'
        ];
        $tipoLabels = [
            'PETICION' => 'Petición',
            'QUEJA' => 'Queja',
            'RECLAMO' => 'Reclamo',
            'SUGERENCIA' => 'Sugerencia',
            'DENUNCIA' => 'Denuncia'
        ];

        $html .= '
                <div class="alerta ' . $clase . '">
                    <div class="alerta-header">
                        <span class="alerta-tipo" style="background: ' . $color . ';">
                            ' . $icono . ' ' . $nivel . '
                        </span>
                        <span class="alerta-dias" style="color: ' . $color . ';">' . $texto_dias . '</span>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <span class="codigo">' . htmlspecialchars($pqrs['codigo_radicado']) . '</span>
                        <span class="estado estado-' . $estadoClass . '">' . ($estadoLabels[$pqrs['estado']] ?? $pqrs['estado']) . '</span>
                    </div>
                    <h3 style="margin: 0 0 10px 0; font-size: 16px; color: #111827;">' . htmlspecialchars($pqrs['asunto']) . '</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Tipo de Solicitud</div>
                            <div class="info-value">' . ($tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud'])) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Solicitante</div>
                            <div class="info-value">' . htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo') . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Fecha de Radicación</div>
                            <div class="info-value">' . date('d/m/Y', strtotime($pqrs['fecha_radicacion'])) . '</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Fecha de Vencimiento</div>
                            <div class="info-value" style="color: ' . $color . '; font-weight: bold;">' . date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])) . '</div>
                        </div>
                    </div>
                </div>';
    }

    $html .= '
            </div>
            <div class="footer">
                <p><strong>Este es un mensaje automático del Sistema PQRS</strong></p>
                <p>Generado el: ' . $fecha_actual . '</p>
                <p style="margin-top: 10px; font-size: 11px;">
                    Para ver más detalles, ingrese al panel de administración.
                </p>
            </div>
        </div>
    </body>
    </html>';

    return $html;
}

/**
 * Guarda alerta en tabla alerta_vencimiento
 */
function guardarAlerta($pqrs_id, $dias_restantes, $nivel) {
    $con = conexion();
    if (!$con) return false;

    $check = $con->prepare("SELECT id FROM alerta_vencimiento 
                           WHERE pqrs_id = ? 
                           AND notificacion_enviada = FALSE 
                           AND nivel_alerta = ?");
    $check->bind_param("is", $pqrs_id, $nivel);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        mysqli_close($con);
        return true;
    }
    $check->close();

    $stmt = $con->prepare("INSERT INTO alerta_vencimiento 
                          (pqrs_id, dias_restantes, nivel_alerta, notificacion_enviada) 
                          VALUES (?, ?, ?, FALSE)");
    $stmt->bind_param("iis", $pqrs_id, $dias_restantes, $nivel);
    $result = $stmt->execute();
    $stmt->close();
    mysqli_close($con);

    return $result;
}

/**
 * Marca alertas como enviadas
 */
function marcarAlertasEnviadas($pqrs_list) {
    $con = conexion();
    if (!$con) return;

    $ids = array_map(function($p) { return intval($p['id']); }, $pqrs_list);
    
    if (empty($ids)) {
        mysqli_close($con);
        return;
    }
    
    $ids_str = implode(',', $ids);
    
    mysqli_query($con, "UPDATE alerta_vencimiento 
                        SET notificacion_enviada = TRUE, 
                            fecha_generacion = NOW() 
                        WHERE pqrs_id IN ($ids_str) 
                        AND notificacion_enviada = FALSE");
    mysqli_close($con);
}

/**
 * Obtiene el correo de notificaciones desde configuracion_sistema
 */
function obtenerCorreoNotificaciones() {
    $con = conexion();
    if (!$con) return '';
    
    $result = mysqli_query($con, "SELECT correo_notificaciones FROM configuracion_sistema WHERE id = 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $correo = $row['correo_notificaciones'];
        mysqli_close($con);
        return $correo;
    }
    mysqli_close($con);
    return '';
}

// Ejecutar si se llama directamente
if (php_sapi_name() === 'cli' || isset($_GET['ejecutar'])) {
    error_log("=== INICIO CRON ALERTAS PQRS === " . date('Y-m-d H:i:s'));
    
    $resultado = enviarAlertasEmail();
    
    if ($resultado) {
        error_log("CRON ALERTAS: Ejecución exitosa");
    } else {
        error_log("CRON ALERTAS: Ejecución fallida");
    }
    
    error_log("=== FIN CRON ALERTAS PQRS === " . date('Y-m-d H:i:s'));
    
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $resultado,
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => $resultado ? 'Alertas procesadas correctamente' : 'Error al procesar alertas'
        ]);
    }
}