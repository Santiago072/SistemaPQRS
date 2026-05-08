<?php
/* HU-Detalle y Respuesta a Solicitudes: Vista completa de PQRS con todos los datos */

include '../includes/verificar_sesion.php';
include '../config/conexion.php';
include '../includes/funciones.php'; // <-- CORREGIDO: Incluir funciones

$con = conexion();

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: pqrs.php');
    exit;
}

// Obtener datos de la PQRS según esquema SQL correcto
$query = "SELECT p.*, u.nombre_completo, u.tipo_persona, u.tipo_documento, u.documento_identidad,
                 u.correo_electronico, u.telefono,
                 u.razon_social, u.nit, u.nombre_representante, u.correo_corporativo,
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

// Obtener historial de acciones (tabla historial_accion según SQL)
$query_historial = "SELECT h.*, a.nombre_completo as admin_nombre 
                    FROM historial_accion h 
                    LEFT JOIN administrador a ON h.administrador_id = a.id 
                    WHERE h.pqrs_id = ? 
                    ORDER BY h.fecha_hora DESC 
                    LIMIT 10";
$stmt_hist = $con->prepare($query_historial);
$stmt_hist->bind_param("i", $id);
$stmt_hist->execute();
$historial = $stmt_hist->get_result()->fetch_all(MYSQLI_ASSOC);

// Registrar visualización
registrarAccion('VISUALIZACION', "Vista del detalle de PQRS", $id);

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

// Determinar urgencia
$diasRestantes = $pqrs['dias_restantes'];
$urgenciaClass = '';
$urgenciaText = '';
if ($pqrs['estado'] !== 'RESUELTO' && $pqrs['estado'] !== 'RECHAZADO') {
    if ($diasRestantes < 0) {
        $urgenciaClass = 'urgencia-vencida';
        $urgenciaText = 'VENCIDA hace ' . abs($diasRestantes) . ' días';
    } elseif ($diasRestantes <= 5) {
        $urgenciaClass = 'urgencia-critico';
        $urgenciaText = 'CRÍTICO - ' . $diasRestantes . ' días restantes';
    } elseif ($diasRestantes <= 10) {
        $urgenciaClass = 'urgencia-urgente';
        $urgenciaText = 'URGENTE - ' . $diasRestantes . ' días restantes';
    } elseif ($diasRestantes <= 15) {
        $urgenciaClass = 'urgencia-moderado';
        $urgenciaText = 'Moderado - ' . $diasRestantes . ' días restantes';
    } else {
        $urgenciaText = $diasRestantes . ' días restantes';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle PQRS <?php echo htmlspecialchars($pqrs['codigo_radicado']); ?> - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <section class="dashboard-section">
        <div class="container">
            <!-- Navegación -->
            <div class="detalle-nav">
                <a href="pqrs.php" class="btn-volver-detalle">
                    <i class="bi bi-arrow-left"></i>
                    Volver a la bandeja
                </a>
                <div class="detalle-acciones">
                    <a href="pqrs_responder.php?id=<?php echo $id; ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-reply"></i>
                        Responder
                    </a>
                    <a href="pqrs_historial.php?id=<?php echo $id; ?>" class="btn btn-sm">
                        <i class="bi bi-clock-history"></i>
                        Historial completo
                    </a>
                </div>
            </div>

            <!-- Encabezado de la solicitud -->
            <div class="detalle-header">
                <div class="detalle-header-main">
                    <div class="detalle-codigo">
                        <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                            <?php echo $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']); ?>
                        </span>
                        <h1><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></h1>
                    </div>
                    <div class="detalle-estado">
                        <span class="estado-tag estado-<?php echo strtolower(str_replace('_', '-', $pqrs['estado'])); ?>">
                            <?php echo $estadoLabels[$pqrs['estado']] ?? $pqrs['estado']; ?>
                        </span>
                        <?php if ($urgenciaText): ?>
                        <span class="urgencia-badge <?php echo $urgenciaClass; ?>">
                            <i class="bi bi-clock"></i>
                            <?php echo $urgenciaText; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="detalle-fechas">
                    <span><i class="bi bi-calendar-plus"></i> Radicado: <?php echo date('d/m/Y H:i', strtotime($pqrs['fecha_radicacion'])); ?></span>
                    <?php if ($pqrs['fecha_vencimiento']): ?>
                    <span><i class="bi bi-calendar-x"></i> Vence: <?php echo date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detalle-grid">
                <!-- Columna principal -->
                <div class="detalle-main">
                    <!-- Asunto y descripción -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-file-text"></i> Información de la Solicitud</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="info-grupo">
                                <label>Asunto</label>
                                <p class="asunto-text"><?php echo htmlspecialchars($pqrs['asunto']); ?></p>
                            </div>
                            <div class="info-grupo">
                                <label>Descripción</label>
                                <div class="descripcion-text"><?php echo nl2br(htmlspecialchars($pqrs['descripcion'])); ?></div>
                            </div>
                            <?php if ($pqrs['archivo_adjunto']): ?>
                            <div class="info-grupo">
                                <label>Archivo Adjunto</label>
                                <a href="../uploads/<?php echo htmlspecialchars($pqrs['archivo_adjunto']); ?>" class="adjunto-link" target="_blank">
                                    <i class="bi bi-paperclip"></i>
                                    <?php echo htmlspecialchars($pqrs['archivo_adjunto']); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Respuesta del administrador (si existe) -->
                    <?php if ($pqrs['respuesta_administrador']): ?>
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-chat-square-text"></i> Respuesta Oficial</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="respuesta-item respuesta-publica">
                                <div class="respuesta-header">
                                    <div class="respuesta-autor">
                                        <i class="bi bi-person-circle"></i>
                                        <strong>Administración</strong>
                                        <span class="badge badge-green"><i class="bi bi-eye"></i> Visible al ciudadano</span>
                                    </div>
                                    <?php if ($pqrs['fecha_respuesta']): ?>
                                    <span class="respuesta-fecha">
                                        <?php echo date('d/m/Y H:i', strtotime($pqrs['fecha_respuesta'])); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="respuesta-contenido">
                                    <?php echo nl2br(htmlspecialchars($pqrs['respuesta_administrador'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-chat-square-text"></i> Respuesta</h2>
                        </div>
                        <div class="detalle-card-body">
                            <div class="sin-respuestas">
                                <i class="bi bi-chat-square"></i>
                                <p>No hay respuesta registrada aún.</p>
                                <a href="pqrs_responder.php?id=<?php echo $id; ?>" class="btn btn-sm">
                                    <i class="bi bi-reply"></i> Agregar respuesta
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Historial reciente - HU-Historial de Acciones -->
                    <?php if (!empty($historial)): ?>
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-clock-history"></i> Historial Reciente</h2>
                            <a href="pqrs_historial.php?id=<?php echo $id; ?>" class="btn-ver-mas">Ver todo</a>
                        </div>
                        <div class="detalle-card-body">
                            <div class="historial-mini">
                                <?php foreach (array_slice($historial, 0, 5) as $accion): ?>
                                <div class="historial-item">
                                    <div class="historial-icon">
                                        <i class="bi bi-<?php echo match($accion['accion_realizada']) {
                                            'CAMBIO_ESTADO' => 'flag',
                                            'RESPUESTA' => 'chat-square-text',
                                            'VISUALIZACION' => 'eye',
                                            default => 'activity'
                                        }; ?>"></i>
                                    </div>
                                    <div class="historial-info">
                                        <strong><?php echo htmlspecialchars($accion['accion_realizada']); ?></strong>
                                        <p><?php echo htmlspecialchars($accion['descripcion'] ?? ''); ?></p>
                                        <small>
                                            <?php echo htmlspecialchars($accion['admin_nombre'] ?? 'Sistema'); ?> -
                                            <?php echo date('d/m/Y H:i', strtotime($accion['fecha_hora'])); ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Columna lateral - HU-Detalle: Muestra todos los datos del solicitante según tipo de persona -->
                <div class="detalle-sidebar">
                    <!-- Datos del solicitante -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-person"></i> Datos del Solicitante</h2>
                        </div>
                        <div class="detalle-card-body">
                            <?php if (strtoupper($pqrs['tipo_persona']) === 'ANONIMA'): ?>
                                <div class="solicitante-anonimo">
                                    <i class="bi bi-incognito"></i>
                                    <span>Solicitud Anónima</span>
                                </div>
                            <?php elseif (strtoupper($pqrs['tipo_persona']) === 'JURIDICA'): ?>
                                <!-- Persona Jurídica -->
                                <div class="info-item">
                                    <label>Tipo</label>
                                    <span class="badge badge-green">Persona Jurídica</span>
                                </div>
                                <div class="info-item">
                                    <label>Razón Social</label>
                                    <span><?php echo htmlspecialchars($pqrs['razon_social'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>NIT</label>
                                    <span><?php echo htmlspecialchars($pqrs['nit'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Representante Legal</label>
                                    <span><?php echo htmlspecialchars($pqrs['nombre_representante'] ?? 'N/A'); ?></span>
                                </div>
                                <?php if ($pqrs['correo_corporativo']): ?>
                                <div class="info-item">
                                    <label>Correo Corporativo</label>
                                    <span><?php echo htmlspecialchars($pqrs['correo_corporativo']); ?></span>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Persona Natural -->
                                <div class="info-item">
                                    <label>Tipo</label>
                                    <span class="badge badge-yellow">Persona Natural</span>
                                </div>
                                <div class="info-item">
                                    <label>Nombre</label>
                                    <span><?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="info-item">
                                    <label>Documento</label>
                                    <span><?php echo htmlspecialchars(($pqrs['tipo_documento'] ?? '') . ' ' . ($pqrs['documento_identidad'] ?? 'N/A')); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (strtoupper($pqrs['tipo_persona']) !== 'ANONIMA'): ?>
                            <hr class="info-divider">
                            <div class="info-item">
                                <label><i class="bi bi-envelope"></i> Correo</label>
                                <span>
                                    <?php if ($pqrs['correo_electronico']): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($pqrs['correo_electronico']); ?>">
                                        <?php echo htmlspecialchars($pqrs['correo_electronico']); ?>
                                    </a>
                                    <?php else: ?>
                                    N/A
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label><i class="bi bi-telephone"></i> Teléfono</label>
                                <span><?php echo htmlspecialchars($pqrs['telefono'] ?? 'N/A'); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Cambio rápido de estado - HU-Detalle: Cambio de estado -->
                    <div class="detalle-card">
                        <div class="detalle-card-header">
                            <h2><i class="bi bi-flag"></i> Cambiar Estado</h2>
                        </div>
                        <div class="detalle-card-body">
                            <form action="pqrs_cambiar_estado.php" method="POST" class="cambio-estado-form">
                                <input type="hidden" name="pqrs_id" value="<?php echo $id; ?>">
                                <input type="hidden" name="redirect" value="pqrs_ver.php?id=<?php echo $id; ?>">

                                <div class="estado-actual">
                                    Estado actual: 
                                    <span class="estado-tag estado-<?php echo strtolower(str_replace('_', '-', $pqrs['estado'])); ?>">
                                        <?php echo $estadoLabels[$pqrs['estado']] ?? $pqrs['estado']; ?>
                                    </span>
                                </div>

                                <select name="nuevo_estado" class="filtro-select" required>
                                    <option value="">Seleccionar nuevo estado</option>
                                    <?php if ($pqrs['estado'] === 'PENDIENTE'): ?>
                                        <option value="EN_PROCESO">En Proceso</option>
                                    <?php endif; ?>
                                    <?php if ($pqrs['estado'] === 'PENDIENTE' || $pqrs['estado'] === 'EN_PROCESO'): ?>
                                        <option value="RESUELTO">Resuelto</option>
                                        <option value="RECHAZADO">Rechazado</option>
                                    <?php endif; ?>
                                </select>

                                <textarea name="comentario" class="form-textarea" rows="3" placeholder="Comentario opcional sobre el cambio..."></textarea>

                                <?php if ($pqrs['estado'] !== 'RESUELTO' && $pqrs['estado'] !== 'RECHAZADO'): ?>
                                <button type="submit" class="btn-enviar" style="width: 100%; margin-top: 1rem;">
                                    <i class="bi bi-check-circle"></i>
                                    Actualizar Estado
                                </button>
                                <?php else: ?>
                                <p class="text-muted" style="margin-top: 1rem; font-size: 0.875rem;">
                                    <i class="bi bi-info-circle"></i>
                                    Esta solicitud ya ha sido cerrada.
                                </p>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
</body>
</html>