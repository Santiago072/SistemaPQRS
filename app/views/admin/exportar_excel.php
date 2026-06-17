<?php
/* HU-Generación de Reportes: Exportación de reportes en Excel (CSV compatible) */

include __DIR__ . '/../layouts/verificar_sesion.php';
include __DIR__ . '/../../../config/conexion.php';

$con = conexion();

// Obtener filtros
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$filtro_fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$filtro_tipo = $_GET['tipo'] ?? '';

// Construir where clause (sin campo 'area' que no existe en SQL)
$where_conditions = ["DATE(p.fecha_radicacion) BETWEEN '$filtro_fecha_inicio' AND '$filtro_fecha_fin'"];

if (!empty($filtro_tipo)) {
    $where_conditions[] = "p.tipo_solicitud = '" . mysqli_real_escape_string($con, $filtro_tipo) . "'";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Obtener datos según campos del SQL
$query = "SELECT 
            p.codigo_radicado,
            p.tipo_solicitud,
            u.tipo_persona,
            p.asunto,
            u.nombre_completo as solicitante,
            u.correo_electronico,
            u.telefono,
            p.fecha_radicacion,
            p.fecha_vencimiento,
            p.estado,
            p.fecha_actualizacion,
            DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes,
            CASE 
                WHEN p.estado = 'RESUELTO' AND p.fecha_actualizacion <= p.fecha_vencimiento THEN 'SI'
                WHEN p.estado = 'RESUELTO' AND p.fecha_actualizacion > p.fecha_vencimiento THEN 'NO'
                ELSE 'N/A'
            END as dentro_terminos
          FROM pqrs p 
          LEFT JOIN usuario u ON p.usuario_id = u.id 
          $where_clause 
          ORDER BY p.fecha_radicacion DESC";

$result = mysqli_query($con, $query);

// Calcular métricas para persistir en tabla reporte
$query_met = "SELECT estado, COUNT(*) as cantidad FROM pqrs p $where_clause GROUP BY estado";
$res_met   = mysqli_query($con, $query_met);
$estados_xls = [];
while ($r = mysqli_fetch_assoc($res_met)) {
    $estados_xls[$r['estado']] = (int)$r['cantidad'];
}
$total_rec_xls  = array_sum($estados_xls);
$total_res_xls  = $estados_xls['RESUELTO']   ?? 0;
$total_pen_xls  = ($estados_xls['PENDIENTE']  ?? 0) + ($estados_xls['EN_PROCESO'] ?? 0);
$total_rech_xls = $estados_xls['RECHAZADO']  ?? 0;

$query_tp_xls = "SELECT AVG(DATEDIFF(COALESCE(p.fecha_respuesta, p.fecha_actualizacion), p.fecha_radicacion)) as promedio
                 FROM pqrs p $where_clause AND p.estado = 'RESUELTO'";
$tiempo_prom_xls = round(mysqli_fetch_assoc(mysqli_query($con, $query_tp_xls))['promedio'] ?? 0, 1);

$query_cumpl_xls = "SELECT SUM(CASE WHEN p.fecha_actualizacion <= p.fecha_vencimiento THEN 1 ELSE 0 END) as en_tiempo
                    FROM pqrs p $where_clause AND p.estado = 'RESUELTO' AND p.fecha_vencimiento IS NOT NULL";
$en_tiempo_xls = (int)(mysqli_fetch_assoc(mysqli_query($con, $query_cumpl_xls))['en_tiempo'] ?? 0);
$porcentaje_cumpl_xls = $total_res_xls > 0
    ? round(($en_tiempo_xls / $total_res_xls) * 100, 2)
    : 0;

$tipo_rep_xls  = !empty($filtro_tipo) ? strtoupper($filtro_tipo) : 'GENERAL';
$admin_id_xls  = $_SESSION['admin_id'] ?? null;

$stmt_rep_xls = $con->prepare(
    "INSERT INTO reporte (
        tipo_reporte, fecha_inicio, fecha_fin,
        total_recibidas, total_resueltas, total_pendientes, total_rechazadas,
        tiempo_promedio_respuesta, porcentaje_cumplimiento,
        formato_exportacion, administrador_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'EXCEL', ?)"
);
if ($stmt_rep_xls) {
    $stmt_rep_xls->bind_param(
        'sssiiiiddi',
        $tipo_rep_xls,
        $filtro_fecha_inicio,
        $filtro_fecha_fin,
        $total_rec_xls,
        $total_res_xls,
        $total_pen_xls,
        $total_rech_xls,
        $tiempo_prom_xls,
        $porcentaje_cumpl_xls,
        $admin_id_xls
    );
    $stmt_rep_xls->execute();
    $stmt_rep_xls->close();
}

mysqli_close($con);

$tipoLabels = [
    'peticion' => 'Petición',
    'queja' => 'Queja',
    'reclamo' => 'Reclamo',
    'sugerencia' => 'Sugerencia',
    'denuncia' => 'Denuncia'
];

// Etiquetas según SQL: tipo_persona puede ser NATURAL, JURIDICA, ANONIMA
$tipoPersonaLabels = [
    'NATURAL' => 'Persona Natural',
    'JURIDICA' => 'Persona Jurídica',
    'ANONIMA' => 'Anónimo'
];

$estadoLabels = [
    'PENDIENTE' => 'Pendiente',
    'EN_PROCESO' => 'En Proceso',
    'RESUELTO' => 'Resuelto',
    'RECHAZADO' => 'Rechazado'
];

// Nombre del archivo
$filename = 'Reporte_PQRS_' . date('Y-m-d_His') . '.xls';

// Headers para Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM para UTF-8 en Excel
echo "\xEF\xBB\xBF";

// Generar contenido HTML para Excel
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
    <meta charset="UTF-8">
    <!--[if gte mso 9]>
    <xml>
        <x:ExcelWorkbook>
            <x:ExcelWorksheets>
                <x:ExcelWorksheet>
                    <x:Name>Reporte PQRS</x:Name>
                    <x:WorksheetOptions>
                        <x:Selected/>
                        <x:FreezePanes/>
                        <x:FrozenNoSplit/>
                        <x:SplitHorizontal>1</x:SplitHorizontal>
                        <x:TopRowBottomPane>1</x:TopRowBottomPane>
                    </x:WorksheetOptions>
                </x:ExcelWorksheet>
            </x:ExcelWorksheets>
        </x:ExcelWorkbook>
    </xml>
    <![endif]-->
    <style>
        table { border-collapse: collapse; }
        th { background-color: #1e40af; color: white; font-weight: bold; padding: 8px; border: 1px solid #ccc; }
        td { padding: 6px; border: 1px solid #ccc; }
        .header-info { font-weight: bold; background-color: #f3f4f6; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <table>
        <!-- Información del reporte -->
        <tr>
            <td colspan="12" class="header-info">SISTEMA PQRS - REPORTE DE GESTIÓN</td>
        </tr>
        <tr>
            <td colspan="12">Generado por: <?php echo htmlspecialchars($adminNombre); ?> | Fecha: <?php echo date('d/m/Y H:i'); ?></td>
        </tr>
        <tr>
            <td colspan="12">Período: <?php echo date('d/m/Y', strtotime($filtro_fecha_inicio)); ?> al <?php echo date('d/m/Y', strtotime($filtro_fecha_fin)); ?></td>
        </tr>
        <tr><td colspan="12"></td></tr>
        
        <!-- Encabezados -->
        <tr>
            <th>Código Radicado</th>
            <th>Tipo Solicitud</th>
            <th>Tipo Persona</th>
            <th>Asunto</th>
            <th>Solicitante</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Fecha Radicación</th>
            <th>Fecha Vencimiento</th>
            <th>Estado</th>
            <th>Días Restantes</th>
            <th>Dentro de Términos</th>
        </tr>
        
        <!-- Datos -->
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td class="text-left"><?php echo htmlspecialchars($row['codigo_radicado']); ?></td>
            <td><?php echo $tipoLabels[$row['tipo_solicitud']] ?? ucfirst($row['tipo_solicitud']); ?></td>
            <td><?php echo $tipoPersonaLabels[strtoupper($row['tipo_persona'] ?? '')] ?? ucfirst($row['tipo_persona'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($row['asunto']); ?></td>
            <td><?php echo htmlspecialchars($row['solicitante'] ?? 'Anónimo'); ?></td>
            <td><?php echo htmlspecialchars($row['correo_electronico'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($row['telefono'] ?? 'N/A'); ?></td>
            <td class="text-center"><?php echo date('d/m/Y', strtotime($row['fecha_radicacion'])); ?></td>
            <td class="text-center"><?php echo $row['fecha_vencimiento'] ? date('d/m/Y', strtotime($row['fecha_vencimiento'])) : 'N/A'; ?></td>
            <td><?php echo $estadoLabels[$row['estado']] ?? $row['estado']; ?></td>
            <td class="text-center"><?php 
                if ($row['estado'] === 'RESUELTO' || $row['estado'] === 'RECHAZADO') {
                    echo 'Cerrado';
                } elseif ($row['dias_restantes'] !== null) {
                    echo $row['dias_restantes'] < 0 ? 'Vencida (' . abs($row['dias_restantes']) . ')' : $row['dias_restantes'];
                } else {
                    echo 'N/A';
                }
            ?></td>
            <td class="text-center"><?php echo $row['dentro_terminos']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>