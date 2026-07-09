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
$baseUrl = $isRailway ? '/' : '/SistemaPQRS/';
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

            <!-- Métricas principales - HU-Reportes: total recibidas, resueltas, pendientes, rechazadas, tiempo promedio -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="bi bi-inbox-fill"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo number_format($metricas['total_recibidas']); ?></span>
                        <span class="stat-label">Total Recibidas</span>
                    </div>
                </div>
                
                <div class="stat-card stat-resuelto">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo number_format($metricas['por_estado']['RESUELTO'] ?? 0); ?></span>
                        <span class="stat-label">Resueltas</span>
                    </div>
                </div>
                
                <div class="stat-card stat-pendiente">
                    <div class="stat-icon">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo number_format(($metricas['por_estado']['PENDIENTE'] ?? 0) + ($metricas['por_estado']['EN_PROCESO'] ?? 0)); ?></span>
                        <span class="stat-label">Pendientes</span>
                    </div>
                </div>
                
                <div class="stat-card stat-vencida">
                    <div class="stat-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo number_format($metricas['por_estado']['RECHAZADO'] ?? 0); ?></span>
                        <span class="stat-label">Rechazadas</span>
                    </div>
                </div>
                
                <div class="stat-card stat-mes">
                    <div class="stat-icon">
                        <i class="bi bi-stopwatch-fill"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-num"><?php echo $metricas['tiempo_promedio']; ?></span>
                        <span class="stat-label">Días promedio</span>
                    </div>
                </div>
            </div>

            <!-- Gráficos - HU-Reportes: Visualización en gráficos -->
            <div class="graficos-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <!-- Gráfico por tipo -->
                <div class="grafico-card" style="background: #fff; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                    <div class="grafico-header" style="margin-bottom: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.5rem;">
                        <h3 style="font-size: 1.1rem; color: #111827; margin:0;"><i class="bi bi-pie-chart" style="color: #3b82f6;"></i> Distribución por Tipo</h3>
                    </div>
                    <div class="grafico-body" style="position: relative; height: 250px; width: 100%;">
                        <canvas id="chartTipo"></canvas>
                    </div>
                </div>
                
                <!-- Gráfico por estado -->
                <div class="grafico-card" style="background: #fff; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                    <div class="grafico-header" style="margin-bottom: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.5rem;">
                        <h3 style="font-size: 1.1rem; color: #111827; margin:0;"><i class="bi bi-bar-chart" style="color: #10b981;"></i> Distribución por Estado</h3>
                    </div>
                    <div class="grafico-body" style="position: relative; height: 250px; width: 100%;">
                        <canvas id="chartEstado"></canvas>
                    </div>
                </div>
                
                <!-- Gráfico tendencia mensual -->
                <div class="grafico-card grafico-full" style="grid-column: 1 / -1; background: #fff; border-radius: 1rem; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #f3f4f6;">
                    <div class="grafico-header" style="margin-bottom: 1rem; border-bottom: 1px solid #f3f4f6; padding-bottom: 0.5rem;">
                        <h3 style="font-size: 1.1rem; color: #111827; margin:0;"><i class="bi bi-graph-up" style="color: #8b5cf6;"></i> Tendencia Mensual (Últimos 6 meses)</h3>
                    </div>
                    <div class="grafico-body" style="position: relative; height: 280px; width: 100%;">
                        <canvas id="chartTendencia"></canvas>
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
                maintainAspectRatio: false,
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
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Gráfico tendencia (Line)
        new Chart(document.getElementById('chartTendencia'), {
            type: 'line',
            data: {
                labels: tendenciaMeses,
                datasets: [
                    {
                        label: 'Total recibidas',
                        data: tendenciaTotal,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Resueltas',
                        data: tendenciaResueltas,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: { 
                    legend: { position: 'top' },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleFont: { size: 13 },
                        bodyFont: { size: 13 },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: { 
                    y: { 
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], color: '#f3f4f6' },
                        ticks: { stepSize: 1, precision: 0 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>
