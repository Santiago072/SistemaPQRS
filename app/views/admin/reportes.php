<?php
/* HU-Generación de Reportes: Dashboard de reportes con filtros y métricas 
 * Filtros por tipo de solicitud, tiempos de respuesta
 * Métricas: total recibidas, resueltas, pendientes, tiempo promedio
 * Visualización en gráficos/tabla
 */

/* HU-Generación de Reportes: Dashboard de reportes con filtros y métricas */

$tipoLabels = [
    'peticion' => 'Petición',
    'queja' => 'Queja',
    'reclamo' => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia' => 'Denuncia'
];

$meses = ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun', 
          '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <?php
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
$baseUrl = $isRailway ? '/' : '/PROYECTO_PQRS/';
?>
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>public/css/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <i class="bi bi-bar-chart-fill"></i>
                        Reportes y Estadísticas
                    </h1>
                    <p class="dashboard-subtitle">
                        Análisis de desempeño institucional en gestión de PQRS
                    </p>
                </div>
                <!-- HU-Reportes: Exportación en PDF y Excel -->
                <div class="dashboard-meta">
                    <?php 
                        $queryParams = $_GET;
                        unset($queryParams['ruta']);
                        $queryString = http_build_query($queryParams);
                        $queryString = $queryString ? '&' . $queryString : '';
                    ?>
                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/exportar_pdf<?php echo $queryString; ?>" class="btn btn-sm" target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                    </a>
                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=admin/exportar_excel<?php echo $queryString; ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                    </a>
                </div>
            </div>

            <!-- Filtros - HU-Reportes: Filtros por tipo de solicitud -->
            <div class="filtros-card">
                <form method="GET" action="<?php echo BASE_PATH; ?>index.php" class="filtros-form">
                    <input type="hidden" name="ruta" value="admin/reportes">
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
                                <i class="bi bi-tag"></i> Tipo
                            </label>
                            <select name="tipo" class="filtro-select">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($tipoLabels as $key => $label): ?>
                                <option value="<?php echo $key; ?>" <?php echo $filtro_tipo === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filtro-acciones">
                            <button type="submit" class="btn-filtrar">
                                <i class="bi bi-funnel"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Métricas principales - HU-Reportes: total recibidas, resueltas, pendientes, tiempo promedio -->
            <div class="metricas-grid">
                <div class="metrica-card metrica-total">
                    <div class="metrica-icon">
                        <i class="bi bi-inbox-fill"></i>
                    </div>
                    <div class="metrica-info">
                        <span class="metrica-num"><?php echo number_format($metricas['total_recibidas']); ?></span>
                        <span class="metrica-label">Total Recibidas</span>
                    </div>
                </div>
                
                <div class="metrica-card metrica-resueltas">
                    <div class="metrica-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="metrica-info">
                        <span class="metrica-num"><?php echo number_format($metricas['por_estado']['RESUELTO'] ?? 0); ?></span>
                        <span class="metrica-label">Resueltas</span>
                    </div>
                </div>
                
                <div class="metrica-card metrica-pendientes">
                    <div class="metrica-icon">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div class="metrica-info">
                        <span class="metrica-num"><?php echo number_format(($metricas['por_estado']['PENDIENTE'] ?? 0) + ($metricas['por_estado']['EN_PROCESO'] ?? 0)); ?></span>
                        <span class="metrica-label">Pendientes</span>
                    </div>
                </div>
                
                <div class="metrica-card metrica-tiempo">
                    <div class="metrica-icon">
                        <i class="bi bi-stopwatch-fill"></i>
                    </div>
                    <div class="metrica-info">
                        <span class="metrica-num"><?php echo $metricas['tiempo_promedio']; ?></span>
                        <span class="metrica-label">Días promedio respuesta</span>
                    </div>
                </div>
            </div>

            <!-- Gráficos - HU-Reportes: Visualización en gráficos -->
            <div class="graficos-grid">
                <!-- Gráfico por tipo -->
                <div class="grafico-card">
                    <div class="grafico-header">
                        <h3><i class="bi bi-pie-chart"></i> Distribución por Tipo</h3>
                    </div>
                    <div class="grafico-body">
                        <canvas id="chartTipo" height="250"></canvas>
                    </div>
                </div>
                
                <!-- Gráfico por estado -->
                <div class="grafico-card">
                    <div class="grafico-header">
                        <h3><i class="bi bi-bar-chart"></i> Distribución por Estado</h3>
                    </div>
                    <div class="grafico-body">
                        <canvas id="chartEstado" height="250"></canvas>
                    </div>
                </div>
                
                <!-- Gráfico tendencia mensual -->
                <div class="grafico-card grafico-full">
                    <div class="grafico-header">
                        <h3><i class="bi bi-graph-up"></i> Tendencia Mensual (Últimos 6 meses)</h3>
                    </div>
                    <div class="grafico-body">
                        <canvas id="chartTendencia" height="120"></canvas>
                    </div>
                </div>
            </div>

            <!-- Cumplimiento de términos -->
            <div class="cumplimiento-card">
                <div class="cumplimiento-header">
                    <h3><i class="bi bi-clipboard-check"></i> Cumplimiento de Términos Legales</h3>
                </div>
                <div class="cumplimiento-body">
                    <div class="cumplimiento-stats">
                        <div class="cumplimiento-stat cumplimiento-ok">
                            <div class="cumplimiento-progress">
                                <?php 
                                $total_terminos = $metricas['en_tiempo'] + $metricas['fuera_tiempo'];
                                $porcentaje_cumplimiento = $total_terminos > 0 ? round(($metricas['en_tiempo'] / $total_terminos) * 100) : 0;
                                ?>
                                <div class="progress-circle" data-percent="<?php echo $porcentaje_cumplimiento; ?>">
                                    <span><?php echo $porcentaje_cumplimiento; ?>%</span>
                                </div>
                            </div>
                            <div class="cumplimiento-info">
                                <span class="cumplimiento-num"><?php echo $metricas['en_tiempo']; ?></span>
                                <span class="cumplimiento-label">Dentro de términos</span>
                            </div>
                        </div>
                        <div class="cumplimiento-stat cumplimiento-fail">
                            <div class="cumplimiento-info">
                                <span class="cumplimiento-num"><?php echo $metricas['fuera_tiempo']; ?></span>
                                <span class="cumplimiento-label">Fuera de términos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla resumen por tipo - HU-Reportes: Visualización en tabla -->
            <div class="detalle-card">
                <div class="detalle-card-header">
                    <h2><i class="bi bi-table"></i> Resumen Detallado por Tipo de Solicitud</h2>
                </div>
                <div class="detalle-card-body">
                    <div class="dashboard-table-wrap">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Porcentaje</th>
                                    <th>Visualización</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($metricas['por_tipo'] as $tipo => $cantidad): 
                                    $porcentaje = $metricas['total_recibidas'] > 0 ? round(($cantidad / $metricas['total_recibidas']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <span class="tipo-badge tipo-<?php echo $tipo; ?>">
                                            <?php echo $tipoLabels[$tipo] ?? ucfirst($tipo); ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo number_format($cantidad); ?></strong></td>
                                    <td><?php echo $porcentaje; ?>%</td>
                                    <td>
                                        <div class="barra-progreso">
                                            <div class="barra-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
    
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Datos para gráficos
        const tipoLabels = <?php echo json_encode(array_map(fn($t) => $tipoLabels[$t] ?? ucfirst($t), array_keys($metricas['por_tipo']))); ?>;
        const tipoData = <?php echo json_encode(array_values($metricas['por_tipo'])); ?>;
        
        const estadoLabels = ['Pendiente', 'En Proceso', 'Resuelto', 'Rechazado'];
        const estadoData = [
            <?php echo $metricas['por_estado']['PENDIENTE'] ?? 0; ?>,
            <?php echo $metricas['por_estado']['EN_PROCESO'] ?? 0; ?>,
            <?php echo $metricas['por_estado']['RESUELTO'] ?? 0; ?>,
            <?php echo $metricas['por_estado']['RECHAZADO'] ?? 0; ?>
        ];
        
        const tendenciaMeses = <?php echo json_encode(array_map(function($m) use ($meses) {
            $parts = explode('-', $m['mes']);
            return $meses[$parts[1]] . ' ' . $parts[0];
        }, $metricas['por_mes'])); ?>;
        const tendenciaTotal = <?php echo json_encode(array_map(fn($m) => (int)$m['total'], $metricas['por_mes'])); ?>;
        const tendenciaResueltas = <?php echo json_encode(array_map(fn($m) => (int)$m['resueltas'], $metricas['por_mes'])); ?>;

        // Gráfico por tipo (Doughnut)
        new Chart(document.getElementById('chartTipo'), {
            type: 'doughnut',
            data: {
                labels: tipoLabels,
                datasets: [{
                    data: tipoData,
                    backgroundColor: ['#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Gráfico por estado (Bar)
        new Chart(document.getElementById('chartEstado'), {
            type: 'bar',
            data: {
                labels: estadoLabels,
                datasets: [{
                    label: 'Cantidad',
                    data: estadoData,
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Gráfico tendencia (Line)
        new Chart(document.getElementById('chartTendencia'), {
            type: 'line',
            data: {
                labels: tendenciaMeses,
                datasets: [{
                    label: 'Total recibidas',
                    data: tendenciaTotal,
                    borderColor: '#1e40af',
                    backgroundColor: 'rgba(30, 64, 175, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Resueltas',
                    data: tendenciaResueltas,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>
