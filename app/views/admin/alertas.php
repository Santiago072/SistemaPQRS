<?php
/* HU-Alertas y Vencimiento: Panel de alertas por vencimiento de PQRS 
 * Alertas a 5, 10 y 15 días según tipo de solicitud
 * Indicadores visuales (colores por urgencia)
 * Notificación visual en el panel
 */
$tipoLabels = [
    'peticion' => 'Petición',
    'queja' => 'Queja',
    'reclamo' => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia' => 'Denuncia'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas de Vencimiento - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
$baseUrl = $isRailway ? '/' : '/SistemaPQRS/';
?>
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>public/css/estilos.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <section class="dashboard-section">
        <div class="container">
            <div class="detalle-nav">
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/dashboard" class="btn-volver-detalle">
                    <i class="bi bi-arrow-left"></i>
                    Volver Dashboard
                </a>
            </div>
            <!-- Encabezado -->
            <div class="dashboard-welcome">
                <div>
                    <h1 class="dashboard-title">
                        <i class="bi bi-bell-fill"></i>
                        Centro de Alertas
                    </h1>
                    <p class="dashboard-subtitle">
                        Monitoreo de PQRS próximas a vencer y vencidas
                    </p>
                </div>
            </div>

            <!-- Resumen de alertas - HU-Alertas: Indicadores visuales -->
            <div class="alertas-resumen">
                <div class="alerta-resumen-card alerta-vencida">
                    <div class="alerta-resumen-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="alerta-resumen-info">
                        <span class="alerta-resumen-num"><?php echo count($alertas_vencidas); ?></span>
                        <span class="alerta-resumen-label">Vencidas</span>
                    </div>
                </div>
                
                <div class="alerta-resumen-card alerta-critico">
                    <div class="alerta-resumen-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div class="alerta-resumen-info">
                        <span class="alerta-resumen-num"><?php echo count($alertas_critico); ?></span>
                        <span class="alerta-resumen-label">Críticas (0-5 días)</span>
                    </div>
                </div>
                
                <div class="alerta-resumen-card alerta-urgente">
                    <div class="alerta-resumen-icon">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div class="alerta-resumen-info">
                        <span class="alerta-resumen-num"><?php echo count($alertas_urgente); ?></span>
                        <span class="alerta-resumen-label">Urgentes (6-10 días)</span>
                    </div>
                </div>
                
                <div class="alerta-resumen-card alerta-moderado">
                    <div class="alerta-resumen-icon">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div class="alerta-resumen-info">
                        <span class="alerta-resumen-num"><?php echo count($alertas_moderado); ?></span>
                        <span class="alerta-resumen-label">Moderadas (11-15 días)</span>
                    </div>
                </div>
            </div>

            <!-- Sección VENCIDAS -->
            <?php if (!empty($alertas_vencidas)): ?>
            <div class="alertas-seccion alertas-seccion-vencida">
                <div class="alertas-seccion-header">
                    <h2>
                        <i class="bi bi-x-circle-fill"></i>
                        PQRS Vencidas
                        <span class="alertas-count"><?php echo count($alertas_vencidas); ?></span>
                    </h2>
                    <span class="alertas-badge badge-red">REQUIERE ATENCIÓN INMEDIATA</span>
                </div>
                <div class="alertas-lista">
                    <?php foreach ($alertas_vencidas as $pqrs): ?>
                    <div class="alerta-item-card">
                        <div class="alerta-item-urgencia urgencia-vencida">
                            <i class="bi bi-x-circle"></i>
                            <span>Vencida hace <?php echo abs($pqrs['dias_restantes']); ?> días</span>
                        </div>
                        <div class="alerta-item-content">
                            <div class="alerta-item-header">
                                <code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code>
                                <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                                    <?php echo $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']); ?>
                                </span>
                            </div>
                            <h3><?php echo htmlspecialchars($pqrs['asunto']); ?></h3>
                            <p>Solicitante: <?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo'); ?></p>
                            <div class="alerta-item-fechas">
                                <span><i class="bi bi-calendar-plus"></i> Radicado: <?php echo date('d/m/Y', strtotime($pqrs['fecha_radicacion'])); ?></span>
                                <span><i class="bi bi-calendar-x"></i> Venció: <?php echo date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])); ?></span>
                            </div>
                        </div>
                        <div class="alerta-item-acciones">
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_ver&id=<?php echo $pqrs['id']; ?>" class="btn btn-sm">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_responder&id=<?php echo $pqrs['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-reply"></i> Responder
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Sección CRÍTICAS - HU-Alertas: 5 días -->
            <?php if (!empty($alertas_critico)): ?>
            <div class="alertas-seccion alertas-seccion-critico">
                <div class="alertas-seccion-header">
                    <h2>
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Alerta Crítica - Vencen en menos de 5 días
                        <span class="alertas-count"><?php echo count($alertas_critico); ?></span>
                    </h2>
                </div>
                <div class="alertas-lista">
                    <?php foreach ($alertas_critico as $pqrs): ?>
                    <div class="alerta-item-card">
                        <div class="alerta-item-urgencia urgencia-critico">
                            <i class="bi bi-exclamation-triangle"></i>
                            <span><?php echo $pqrs['dias_restantes']; ?> día<?php echo $pqrs['dias_restantes'] != 1 ? 's' : ''; ?> restante<?php echo $pqrs['dias_restantes'] != 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="alerta-item-content">
                            <div class="alerta-item-header">
                                <code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code>
                                <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                                    <?php echo $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']); ?>
                                </span>
                            </div>
                            <h3><?php echo htmlspecialchars($pqrs['asunto']); ?></h3>
                            <p>Solicitante: <?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo'); ?></p>
                            <div class="alerta-item-fechas">
                                <span><i class="bi bi-calendar-x"></i> Vence: <?php echo date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])); ?></span>
                            </div>
                        </div>
                        <div class="alerta-item-acciones">
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_ver&id=<?php echo $pqrs['id']; ?>" class="btn btn-sm">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_responder&id=<?php echo $pqrs['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-reply"></i> Responder
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Sección URGENTES - HU-Alertas: 10 días -->
            <?php if (!empty($alertas_urgente)): ?>
            <div class="alertas-seccion alertas-seccion-urgente">
                <div class="alertas-seccion-header">
                    <h2>
                        <i class="bi bi-clock-fill"></i>
                        Alerta Urgente - Vencen entre 6-10 días
                        <span class="alertas-count"><?php echo count($alertas_urgente); ?></span>
                    </h2>
                </div>
                <div class="alertas-lista">
                    <?php foreach ($alertas_urgente as $pqrs): ?>
                    <div class="alerta-item-card">
                        <div class="alerta-item-urgencia urgencia-urgente">
                            <i class="bi bi-clock"></i>
                            <span><?php echo $pqrs['dias_restantes']; ?> días restantes</span>
                        </div>
                        <div class="alerta-item-content">
                            <div class="alerta-item-header">
                                <code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code>
                                <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                                    <?php echo $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']); ?>
                                </span>
                            </div>
                            <h3><?php echo htmlspecialchars($pqrs['asunto']); ?></h3>
                            <p>Solicitante: <?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo'); ?></p>
                        </div>
                        <div class="alerta-item-acciones">
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_ver&id=<?php echo $pqrs['id']; ?>" class="btn btn-sm">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Sección MODERADAS - HU-Alertas: 15 días -->
            <?php if (!empty($alertas_moderado)): ?>
            <div class="alertas-seccion alertas-seccion-moderado">
                <div class="alertas-seccion-header">
                    <h2>
                        <i class="bi bi-info-circle-fill"></i>
                        Alerta Moderada - Vencen entre 11-15 días
                        <span class="alertas-count"><?php echo count($alertas_moderado); ?></span>
                    </h2>
                </div>
                <div class="alertas-lista">
                    <?php foreach ($alertas_moderado as $pqrs): ?>
                    <div class="alerta-item-card">
                        <div class="alerta-item-urgencia urgencia-moderado">
                            <i class="bi bi-info-circle"></i>
                            <span><?php echo $pqrs['dias_restantes']; ?> días restantes</span>
                        </div>
                        <div class="alerta-item-content">
                            <div class="alerta-item-header">
                                <code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code>
                                <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                                    <?php echo $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']); ?>
                                </span>
                            </div>
                            <h3><?php echo htmlspecialchars($pqrs['asunto']); ?></h3>
                        </div>
                        <div class="alerta-item-acciones">
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_ver&id=<?php echo $pqrs['id']; ?>" class="btn btn-sm">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Sin alertas -->
            <?php if (empty($alertas_vencidas) && empty($alertas_critico) && empty($alertas_urgente) && empty($alertas_moderado)): ?>
            <div class="dashboard-empty">
                <i class="bi bi-check-circle"></i>
                <h3>Sin alertas pendientes</h3>
                <p>Todas las PQRS están dentro de los plazos establecidos.</p>
            </div>
            <?php endif; ?>

        </div>
    </section>
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
