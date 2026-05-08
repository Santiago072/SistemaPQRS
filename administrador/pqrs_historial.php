<?php
/* HU-Historial de Acciones: Vista completa del historial de una PQRS 
 * Lista cronológica de cambios con fecha, hora, usuario, acción realizada
 */

include '../includes/verificar_sesion.php';
include '../config/conexion.php';

$con = conexion();

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: pqrs.php');
    exit;
}

// Obtener datos básicos de la PQRS
$query_pqrs = "SELECT codigo_radicado, tipo_solicitud, asunto, estado, fecha_radicacion FROM pqrs WHERE id = ?";
$stmt = $con->prepare($query_pqrs);
$stmt->bind_param("i", $id);
$stmt->execute();
$pqrs = $stmt->get_result()->fetch_assoc();

if (!$pqrs) {
    mysqli_close($con);
    header('Location: pqrs.php?error=not_found');
    exit;
}

// Obtener historial completo de acciones - usando tabla historial_accion (según SQL)
$query_historial = "SELECT h.*, a.nombre_completo as admin_nombre, a.rol as admin_rol
                    FROM historial_accion h 
                    LEFT JOIN administrador a ON h.administrador_id = a.id 
                    WHERE h.pqrs_id = ? 
                    ORDER BY h.fecha_hora DESC";
$stmt_hist = $con->prepare($query_historial);
$stmt_hist->bind_param("i", $id);
$stmt_hist->execute();
$historial = $stmt_hist->get_result()->fetch_all(MYSQLI_ASSOC);

mysqli_close($con);

$tipoLabels = [
    'peticion' => 'Petición',
    'queja' => 'Queja',
    'reclamo' => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia' => 'Denuncia'
];

$estadoLabels = [
    'PENDIENTE' => 'Pendiente',
    'EN_PROCESO' => 'En Proceso',
    'RESUELTO' => 'Resuelto',
    'RECHAZADO' => 'Rechazado'
];

// Iconos para tipos de acción
$accionIconos = [
    'LOGIN' => 'box-arrow-in-right',
    'LOGOUT' => 'box-arrow-right',
    'CAMBIO_ESTADO' => 'flag-fill',
    'RESPUESTA' => 'chat-square-text-fill',
    'VISUALIZACION' => 'eye-fill',
    'CREACION' => 'plus-circle-fill',
    'EDICION' => 'pencil-fill',
    'ENVIO_ALERTAS' => 'envelope-fill',
    'SESSION_EXPIRED' => 'clock-fill',
    'default' => 'activity'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial - <?php echo htmlspecialchars($pqrs['codigo_radicado']); ?> - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/estilos.css">
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

            <!-- Encabezado -->
            <div class="dashboard-welcome">
                <div>
                    <h1 class="dashboard-title">
                        <i class="bi bi-clock-history"></i>
                        Historial de Acciones
                    </h1>
                    <p class="dashboard-subtitle">
                        Trazabilidad completa de la solicitud <code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code>
                    </p>
                </div>
                <div class="dashboard-meta">
                    <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                        <?php echo $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']); ?>
                    </span>
                    <span class="estado-tag estado-<?php echo strtolower(str_replace('_', '-', $pqrs['estado'])); ?>">
                        <?php echo $estadoLabels[$pqrs['estado']] ?? $pqrs['estado']; ?>
                    </span>
                </div>
            </div>

            <!-- Resumen - HU-Historial: Incluye cambios de estado y respuestas enviadas -->
            <div class="historial-resumen">
                <div class="historial-resumen-item">
                    <i class="bi bi-calendar-plus"></i>
                    <span>Radicado: <?php echo date('d/m/Y H:i', strtotime($pqrs['fecha_radicacion'])); ?></span>
                </div>
                <div class="historial-resumen-item">
                    <i class="bi bi-activity"></i>
                    <span><?php echo count($historial); ?> acciones registradas</span>
                </div>
            </div>

            <!-- Timeline - HU-Historial: Lista cronológica con fecha, hora, usuario, acción -->
            <?php if (!empty($historial)): ?>
            <div class="historial-timeline">
                <?php 
                $fecha_actual = '';
                foreach ($historial as $item): 
                    $fecha_item = date('Y-m-d', strtotime($item['fecha_hora']));
                    $mostrar_fecha = $fecha_item !== $fecha_actual;
                    $fecha_actual = $fecha_item;
                    $icono = $accionIconos[$item['accion_realizada']] ?? $accionIconos['default'];
                ?>
                
                <?php if ($mostrar_fecha): ?>
                <div class="timeline-fecha">
                    <span><?php echo date('d F Y', strtotime($item['fecha_hora'])); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="timeline-item timeline-accion">
                    <div class="timeline-icon timeline-icon-accion">
                        <i class="bi bi-<?php echo $icono; ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong><?php echo htmlspecialchars($item['accion_realizada']); ?></strong>
                            <span class="timeline-hora"><?php echo date('H:i', strtotime($item['fecha_hora'])); ?></span>
                        </div>
                        <div class="timeline-body">
                            <p><?php echo htmlspecialchars($item['descripcion'] ?? ''); ?></p>
                            <?php if ($item['estado_anterior'] && $item['estado_nuevo']): ?>
                            <div class="timeline-estado-cambio">
                                <span class="estado-tag estado-<?php echo strtolower(str_replace('_', '-', $item['estado_anterior'])); ?>">
                                    <?php echo $estadoLabels[$item['estado_anterior']] ?? $item['estado_anterior']; ?>
                                </span>
                                <i class="bi bi-arrow-right"></i>
                                <span class="estado-tag estado-<?php echo strtolower(str_replace('_', '-', $item['estado_nuevo'])); ?>">
                                    <?php echo $estadoLabels[$item['estado_nuevo']] ?? $item['estado_nuevo']; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="timeline-footer">
                            <span><i class="bi bi-person"></i> <?php echo htmlspecialchars($item['admin_nombre'] ?? 'Sistema'); ?></span>
                        </div>
                    </div>
                </div>
                
                <?php endforeach; ?>
                
                <!-- Evento de creación (siempre al final) -->
                <div class="timeline-fecha">
                    <span><?php echo date('d F Y', strtotime($pqrs['fecha_radicacion'])); ?></span>
                </div>
                <div class="timeline-item timeline-creacion">
                    <div class="timeline-icon timeline-icon-creacion">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong>Solicitud Radicada</strong>
                            <span class="timeline-hora"><?php echo date('H:i', strtotime($pqrs['fecha_radicacion'])); ?></span>
                        </div>
                        <div class="timeline-body">
                            <p>Se creó la solicitud con código <code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code></p>
                            <p><strong>Asunto:</strong> <?php echo htmlspecialchars($pqrs['asunto']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="historial-timeline">
                <!-- Solo evento de creación -->
                <div class="timeline-fecha">
                    <span><?php echo date('d F Y', strtotime($pqrs['fecha_radicacion'])); ?></span>
                </div>
                <div class="timeline-item timeline-creacion">
                    <div class="timeline-icon timeline-icon-creacion">
                        <i class="bi bi-plus-circle-fill"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong>Solicitud Radicada</strong>
                            <span class="timeline-hora"><?php echo date('H:i', strtotime($pqrs['fecha_radicacion'])); ?></span>
                        </div>
                        <div class="timeline-body">
                            <p>Se creó la solicitud con código <code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code></p>
                            <p><strong>Asunto:</strong> <?php echo htmlspecialchars($pqrs['asunto']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>