<?php
/* HU-Generación de Reportes: Exportación de reportes en PDF usando DomPdf */

include '../includes/verificar_sesion.php';
include '../config/conexion.php';

// CORREGIDO: Ruta correcta del autoload de DomPDF según tu estructura
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$con = conexion();

// Obtener filtros
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$filtro_fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$filtro_tipo = $_GET['tipo'] ?? '';

// Construir where clause
$where_conditions = ["DATE(p.fecha_radicacion) BETWEEN '$filtro_fecha_inicio' AND '$filtro_fecha_fin'"];

if (!empty($filtro_tipo)) {
    $where_conditions[] = "p.tipo_solicitud = '" . mysqli_real_escape_string($con, $filtro_tipo) . "'";
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Obtener métricas
$metricas = [];

// Total
$query = "SELECT COUNT(*) as total FROM pqrs p $where_clause";
$metricas['total'] = mysqli_fetch_assoc(mysqli_query($con, $query))['total'];

// Por estado
$query = "SELECT estado, COUNT(*) as cantidad FROM pqrs p $where_clause GROUP BY estado";
$result = mysqli_query($con, $query);
$metricas['por_estado'] = [];
while ($row = mysqli_fetch_assoc($result)) {
    $metricas['por_estado'][$row['estado']] = $row['cantidad'];
}

// Por tipo
$query = "SELECT tipo_solicitud, COUNT(*) as cantidad FROM pqrs p $where_clause GROUP BY tipo_solicitud ORDER BY cantidad DESC";
$result = mysqli_query($con, $query);
$metricas['por_tipo'] = [];
while ($row = mysqli_fetch_assoc($result)) {
    $metricas['por_tipo'][$row['tipo_solicitud']] = $row['cantidad'];
}

// Tiempo promedio
$query = "SELECT AVG(DATEDIFF(COALESCE(p.fecha_respuesta, p.fecha_actualizacion), p.fecha_radicacion)) as promedio
          FROM pqrs p $where_clause AND p.estado = 'RESUELTO'";
$metricas['tiempo_promedio'] = round(mysqli_fetch_assoc(mysqli_query($con, $query))['promedio'] ?? 0, 1);

// Listado de PQRS
$query = "SELECT p.*, u.nombre_completo, DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
          FROM pqrs p 
          LEFT JOIN usuario u ON p.usuario_id = u.id 
          $where_clause 
          ORDER BY p.fecha_radicacion DESC 
          LIMIT 100";
$result = mysqli_query($con, $query);
$pqrs_list = [];
while ($row = mysqli_fetch_assoc($result)) {
    $pqrs_list[] = $row;
}

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

// CORREGIDO: Usar variable de sesión o genérica para el nombre del admin
$adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';

// Generar HTML para PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; padding: 20px; }

        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1e40af; padding-bottom: 15px; }
        .header h1 { color: #1e40af; font-size: 18pt; margin-bottom: 5px; }
        .header p { color: #666; font-size: 9pt; }

        .info-periodo { background: #f3f4f6; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 9pt; }

        .metricas { display: table; width: 100%; margin-bottom: 25px; }
        .metrica { display: table-cell; text-align: center; padding: 15px; border: 1px solid #e5e7eb; }
        .metrica-num { font-size: 20pt; font-weight: bold; color: #1e40af; }
        .metrica-label { font-size: 8pt; color: #666; display: block; margin-top: 5px; }

        .seccion { margin-bottom: 25px; }
        .seccion h2 { font-size: 12pt; color: #1e40af; margin-bottom: 10px; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; }

        table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        th { background: #1e40af; color: white; padding: 8px 5px; text-align: left; font-weight: bold; }
        td { padding: 6px 5px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 7pt; font-weight: bold; }
        .badge-pendiente { background: #fef3c7; color: #92400e; }
        .badge-en-proceso { background: #dbeafe; color: #1e40af; }
        .badge-resuelto { background: #d1fae5; color: #065f46; }
        .badge-rechazado { background: #fee2e2; color: #991b1b; }

        .footer { position: fixed; bottom: 20px; left: 20px; right: 20px; text-align: center; font-size: 7pt; color: #999; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema PQRS - Reporte de Gestión</h1>
        <p>Generado por: ' . htmlspecialchars($adminNombre) . ' | Fecha: ' . date('d/m/Y H:i') . '</p>
    </div>

    <div class="info-periodo">
        <strong>Período del reporte:</strong> ' . date('d/m/Y', strtotime($filtro_fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($filtro_fecha_fin)) . '
        ' . (!empty($filtro_tipo) ? ' | <strong>Tipo:</strong> ' . ($tipoLabels[$filtro_tipo] ?? $filtro_tipo) : '') . '
    </div>

    <div class="metricas">
        <div class="metrica">
            <span class="metrica-num">' . number_format($metricas['total']) . '</span>
            <span class="metrica-label">Total Recibidas</span>
        </div>
        <div class="metrica">
            <span class="metrica-num">' . number_format($metricas['por_estado']['RESUELTO'] ?? 0) . '</span>
            <span class="metrica-label">Resueltas</span>
        </div>
        <div class="metrica">
            <span class="metrica-num">' . number_format(($metricas['por_estado']['PENDIENTE'] ?? 0) + ($metricas['por_estado']['EN_PROCESO'] ?? 0)) . '</span>
            <span class="metrica-label">Pendientes</span>
        </div>
        <div class="metrica">
            <span class="metrica-num">' . $metricas['tiempo_promedio'] . '</span>
            <span class="metrica-label">Días promedio</span>
        </div>
    </div>

    <div class="seccion">
        <h2>Distribución por Tipo de Solicitud</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>';

foreach ($metricas['por_tipo'] as $tipo => $cantidad) {
    $porcentaje = $metricas['total'] > 0 ? round(($cantidad / $metricas['total']) * 100, 1) : 0;
    $html .= '
                <tr>
                    <td>' . ($tipoLabels[$tipo] ?? ucfirst($tipo)) . '</td>
                    <td>' . number_format($cantidad) . '</td>
                    <td>' . $porcentaje . '%</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <h2>Distribución por Estado</h2>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>';

foreach ($metricas['por_estado'] as $estado => $cantidad) {
    $porcentaje = $metricas['total'] > 0 ? round(($cantidad / $metricas['total']) * 100, 1) : 0;
    $html .= '
                <tr>
                    <td>' . ($estadoLabels[$estado] ?? $estado) . '</td>
                    <td>' . number_format($cantidad) . '</td>
                    <td>' . $porcentaje . '%</td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div class="seccion">
        <h2>Listado de Solicitudes</h2>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Tipo</th>
                    <th>Asunto</th>
                    <th>Solicitante</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>';

foreach ($pqrs_list as $pqrs) {
    $estadoClass = strtolower(str_replace('_', '-', $pqrs['estado']));
    $html .= '
                <tr>
                    <td>' . htmlspecialchars($pqrs['codigo_radicado']) . '</td>
                    <td>' . ($tipoLabels[$pqrs['tipo_solicitud']] ?? $pqrs['tipo_solicitud']) . '</td>
                    <td>' . htmlspecialchars(mb_substr($pqrs['asunto'], 0, 40)) . '</td>
                    <td>' . htmlspecialchars($pqrs['nombre_completo'] ?? 'Anónimo') . '</td>
                    <td>' . date('d/m/Y', strtotime($pqrs['fecha_radicacion'])) . '</td>
                    <td><span class="badge badge-' . $estadoClass . '">' . ($estadoLabels[$pqrs['estado']] ?? $pqrs['estado']) . '</span></td>
                </tr>';
}

$html .= '
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Sistema PQRS - Reporte generado automáticamente | Página <span class="page-number"></span></p>
    </div>
</body>
</html>';

// Configurar DomPdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nombre del archivo
$filename = 'Reporte_PQRS_' . date('Y-m-d_His') . '.pdf';

// Descargar
$dompdf->stream($filename, ['Attachment' => true]);
exit;