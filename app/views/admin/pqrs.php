<?php
/* HU-Bandeja Solicitudes PQRS: Vista de todas las PQRS con filtros */

include __DIR__ . '/../layouts/verificar_sesion.php';
/* HU-Bandeja Solicitudes PQRS: Vista de todas las PQRS con filtros */

$tipoLabels = [
    'peticion' => 'Petición',
    'queja' => 'Queja',
    'reclamo' => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia' => 'Denuncia'
];

// Etiquetas según esquema SQL: tipo_persona en tabla usuario
$tipoPersonaLabels = [
    'NATURAL' => 'Natural',
    'JURIDICA' => 'Jurídica',
    'ANONIMA' => 'Anónimo'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja PQRS - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
$baseUrl = $isRailway ? '/' : '/PROYECTO_PQRS/';
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
                        <i class="bi bi-inbox-fill"></i>
                        Bandeja de Solicitudes PQRS
                    </h1>
                    <p class="dashboard-subtitle">
                        Gestione y dé seguimiento a todas las solicitudes
                    </p>
                </div>
                <div class="dashboard-meta">
                    <span class="dashboard-fecha">
                        <i class="bi bi-collection"></i>
                        <?php echo number_format($total_registros); ?> solicitudes
                    </span>
                </div>
            </div>

            <!-- Alertas de vencimiento - HU-Alertas: Indicadores visuales en la bandeja -->
            <?php if ($alertas['critico'] > 0 || $alertas['urgente'] > 0 || $alertas['moderado'] > 0): ?>
            <div class="alertas-vencimiento">
                <?php if ($alertas['critico'] > 0): ?>
                <a href="?estado=PENDIENTE" class="alerta-item alerta-critico">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><strong><?php echo $alertas['critico']; ?></strong> PQRS vencen en menos de 5 días</span>
                </a>
                <?php endif; ?>
                <?php if ($alertas['urgente'] > 0): ?>
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/alertas" class="alerta-item alerta-urgente">
                    <i class="bi bi-clock-fill"></i>
                    <span><strong><?php echo $alertas['urgente']; ?></strong> PQRS vencen entre 6-10 días</span>
                </a>
                <?php endif; ?>
                <?php if ($alertas['moderado'] > 0): ?>
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/alertas" class="alerta-item alerta-moderado">
                    <i class="bi bi-info-circle-fill"></i>
                    <span><strong><?php echo $alertas['moderado']; ?></strong> PQRS vencen entre 11-15 días</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Estadísticas rápidas -->
            <div class="stats-mini">
                <div class="stat-mini stat-mini-pendiente">
                    <span class="stat-mini-num"><?php echo $estadisticas['PENDIENTE'] ?? 0; ?></span>
                    <span class="stat-mini-label">Pendientes</span>
                </div>
                <div class="stat-mini stat-mini-proceso">
                    <span class="stat-mini-num"><?php echo $estadisticas['EN_PROCESO'] ?? 0; ?></span>
                    <span class="stat-mini-label">En Proceso</span>
                </div>
                <div class="stat-mini stat-mini-resuelto">
                    <span class="stat-mini-num"><?php echo $estadisticas['RESUELTO'] ?? 0; ?></span>
                    <span class="stat-mini-label">Resueltas</span>
                </div>
                <div class="stat-mini stat-mini-rechazado">
                    <span class="stat-mini-num"><?php echo $estadisticas['RECHAZADO'] ?? 0; ?></span>
                    <span class="stat-mini-label">Rechazadas</span>
                </div>
            </div>

            <!-- Filtros - HU-Bandeja: Filtros por estado, tipo, fechas -->
            <div class="filtros-card">
                <form method="GET" action="<?php echo BASE_PATH; ?>index.php" class="filtros-form">
                    <input type="hidden" name="ruta" value="admin/pqrs">
                    <div class="filtros-row">
                        <div class="filtro-grupo">
                            <label class="filtro-label">
                                <i class="bi bi-search"></i> Buscar
                            </label>
                            <input type="text" name="busqueda" class="filtro-input" placeholder="Código, asunto o solicitante..." value="<?php echo htmlspecialchars($busqueda); ?>">
                        </div>
                        
                        <div class="filtro-grupo">
                            <label class="filtro-label">
                                <i class="bi bi-flag"></i> Estado
                            </label>
                            <select name="estado" class="filtro-select">
                                <option value="">Todos los estados</option>
                                <option value="PENDIENTE" <?php echo $filtro_estado === 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="EN_PROCESO" <?php echo $filtro_estado === 'EN_PROCESO' ? 'selected' : ''; ?>>En Proceso</option>
                                <option value="RESUELTO" <?php echo $filtro_estado === 'RESUELTO' ? 'selected' : ''; ?>>Resuelto</option>
                                <option value="RECHAZADO" <?php echo $filtro_estado === 'RECHAZADO' ? 'selected' : ''; ?>>Rechazado</option>
                            </select>
                        </div>
                        
                        <div class="filtro-grupo">
                            <label class="filtro-label">
                                <i class="bi bi-tag"></i> Tipo
                            </label>
                            <select name="tipo" class="filtro-select">
                                <option value="">Todos los tipos</option>
                                <option value="peticion" <?php echo $filtro_tipo === 'peticion' ? 'selected' : ''; ?>>Petición</option>
                                <option value="queja" <?php echo $filtro_tipo === 'queja' ? 'selected' : ''; ?>>Queja</option>
                                <option value="reclamo" <?php echo $filtro_tipo === 'reclamo' ? 'selected' : ''; ?>>Reclamo</option>
                                <option value="sugerencia" <?php echo $filtro_tipo === 'sugerencia' ? 'selected' : ''; ?>>Sugerencia</option>
                                <option value="denuncia" <?php echo $filtro_tipo === 'denuncia' ? 'selected' : ''; ?>>Denuncia</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="filtros-row">
                        <div class="filtro-grupo">
                            <label class="filtro-label">
                                <i class="bi bi-calendar"></i> Desde
                            </label>
                            <input type="date" name="fecha_inicio" class="filtro-input" value="<?php echo htmlspecialchars($filtro_fecha_inicio); ?>">
                        </div>
                        
                        <div class="filtro-grupo">
                            <label class="filtro-label">
                                <i class="bi bi-calendar"></i> Hasta
                            </label>
                            <input type="date" name="fecha_fin" class="filtro-input" value="<?php echo htmlspecialchars($filtro_fecha_fin); ?>">
                        </div>
                        
                        <div class="filtro-grupo">
                            <label class="filtro-label">
                                <i class="bi bi-sort-down"></i> Ordenar
                            </label>
                            <select name="orden" class="filtro-select">
                                <option value="recientes" <?php echo $orden === 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                                <option value="antiguos" <?php echo $orden === 'antiguos' ? 'selected' : ''; ?>>Más antiguos</option>
                                <option value="vencimiento" <?php echo $orden === 'vencimiento' ? 'selected' : ''; ?>>Por vencimiento</option>
                                <option value="codigo" <?php echo $orden === 'codigo' ? 'selected' : ''; ?>>Por código</option>
                            </select>
                        </div>
                        
                        <div class="filtro-acciones">
                            <button type="submit" class="btn-filtrar">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs" class="btn-limpiar">
                                <i class="bi bi-x-circle"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabla de PQRS - HU-Bandeja: Tabla con Código, Tipo, Tipo persona, Asunto, Fecha, Estado -->
            <?php if (!empty($pqrs_list)): ?>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table pqrs-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Tipo Persona</th>
                            <th>Asunto</th>
                            <th>Solicitante</th>
                            <th>Fecha</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pqrs_list as $pqrs): 
                            $estadoClass = strtolower(str_replace('_', '-', $pqrs['estado']));
                            $tipoLabel = $tipoLabels[$pqrs['tipo_solicitud']] ?? ucfirst($pqrs['tipo_solicitud']);
                            $tipoPersonaLabel = $tipoPersonaLabels[strtoupper($pqrs['tipo_persona'] ?? '')] ?? ucfirst($pqrs['tipo_persona'] ?? 'N/A');
                            
                            // Determinar urgencia por días restantes (colores por urgencia - HU-Alertas)
                            $diasRestantes = $pqrs['dias_restantes'];
                            $urgenciaClass = '';
                            if ($pqrs['estado'] !== 'RESUELTO' && $pqrs['estado'] !== 'RECHAZADO') {
                                if ($diasRestantes < 0) {
                                    $urgenciaClass = 'fila-vencida';
                                } elseif ($diasRestantes <= 5) {
                                    $urgenciaClass = 'fila-critico';
                                } elseif ($diasRestantes <= 10) {
                                    $urgenciaClass = 'fila-urgente';
                                } elseif ($diasRestantes <= 15) {
                                    $urgenciaClass = 'fila-moderado';
                                }
                            }
                        ?>
                        <tr class="<?php echo $urgenciaClass; ?>">
                            <td><code><?php echo htmlspecialchars($pqrs['codigo_radicado']); ?></code></td>
                            <td>
                                <span class="tipo-badge tipo-<?php echo $pqrs['tipo_solicitud']; ?>">
                                    <?php echo $tipoLabel; ?>
                                </span>
                            </td>
                            <td><?php echo $tipoPersonaLabel; ?></td>
                            <td class="td-asunto" title="<?php echo htmlspecialchars($pqrs['asunto']); ?>">
                                <?php echo htmlspecialchars(mb_substr($pqrs['asunto'], 0, 40)) . (mb_strlen($pqrs['asunto']) > 40 ? '...' : ''); ?>
                            </td>
                            <td><?php echo htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pqrs['fecha_radicacion'])); ?></td>
                            <td>
                                <?php if ($pqrs['fecha_vencimiento']): ?>
                                    <span class="vencimiento-tag <?php echo $urgenciaClass ? str_replace('fila-', 'venc-', $urgenciaClass) : ''; ?>">
                                        <?php echo date('d/m/Y', strtotime($pqrs['fecha_vencimiento'])); ?>
                                        <?php if ($diasRestantes !== null && $pqrs['estado'] !== 'RESUELTO' && $pqrs['estado'] !== 'RECHAZADO'): ?>
                                            <small>(<?php echo $diasRestantes < 0 ? 'Vencida' : $diasRestantes . 'd'; ?>)</small>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="estado-tag estado-<?php echo $estadoClass; ?>">
                                    <?php echo str_replace('_', ' ', $pqrs['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="acciones-btns">
                                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_ver&id=<?php echo $pqrs['id']; ?>" class="btn-icon btn-ver" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_responder&id=<?php echo $pqrs['id']; ?>" class="btn-icon btn-responder" title="Responder">
                                        <i class="bi bi-reply"></i>
                                    </a>
                                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs_historial&id=<?php echo $pqrs['id']; ?>" class="btn-icon btn-historial" title="Ver historial">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
            <div class="paginacion">
                <div class="paginacion-info">
                    Mostrando <?php echo $offset + 1; ?> - <?php echo min($offset + $por_pagina, $total_registros); ?> de <?php echo $total_registros; ?> solicitudes
                </div>
                <div class="paginacion-btns">
                    <?php if ($pagina > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => 1])); ?>" class="pag-btn" title="Primera">
                        <i class="bi bi-chevron-double-left"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" class="pag-btn" title="Anterior">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <?php 
                    $rango = 2;
                    $inicio = max(1, $pagina - $rango);
                    $fin = min($total_paginas, $pagina + $rango);
                    
                    for ($i = $inicio; $i <= $fin; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>" 
                       class="pag-btn <?php echo $i === $pagina ? 'pag-activo' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($pagina < $total_paginas): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" class="pag-btn" title="Siguiente">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $total_paginas])); ?>" class="pag-btn" title="Última">
                        <i class="bi bi-chevron-double-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="dashboard-empty">
                <i class="bi bi-inbox"></i>
                <h3>No hay solicitudes</h3>
                <p>No se encontraron PQRS con los filtros seleccionados.</p>
                <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/pqrs" class="btn btn-sm">Limpiar filtros</a>
            </div>
            <?php endif; ?>

        </div>
    </section>
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
