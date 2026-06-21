<?php
namespace App\Controllers\Admin;

use App\Models\PqrsModel;

class ReportController
{
    private PqrsModel $pqrsModel;

    public function __construct(PqrsModel $pqrsModel)
    {
        $this->pqrsModel = $pqrsModel;
    }

    public function reportes(): void           
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $filtro_fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $filtro_fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $filtro_tipo = $_GET['tipo'] ?? '';

        $metricas = $this->pqrsModel->obtenerReportesEstadisticas($filtro_fecha_inicio, $filtro_fecha_fin, $filtro_tipo);

        require_once __DIR__ . '/../../../app/views/admin/reportes.php'; 
    }

    public function exportar_excel(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $tipo = $_GET['tipo'] ?? '';

        $resultado = $this->pqrsModel->obtenerParaExportacion($fechaInicio, $fechaFin, $tipo);
        
        $estados = $resultado['estados'];
        $totalRecibidas = array_sum($estados);
        $totalResueltas = $estados['RESUELTO'] ?? 0;
        $totalPendientes = ($estados['PENDIENTE'] ?? 0) + ($estados['EN_PROCESO'] ?? 0);
        $totalRechazadas = $estados['RECHAZADO'] ?? 0;
        $tiempoPromedio = $resultado['tiempo_promedio'];
        $enTiempo = $resultado['en_tiempo'];
        $porcentajeCumplimiento = $totalResueltas > 0 ? round(($enTiempo / $totalResueltas) * 100, 2) : 0;

        $tipoReporte = !empty($tipo) ? strtoupper($tipo) : 'GENERAL';
        $adminId = $_SESSION['admin_id'] ?? null;

        if ($adminId) {
            $this->pqrsModel->registrarReporte(
                $tipoReporte, $fechaInicio, $fechaFin,
                $totalRecibidas, $totalResueltas, $totalPendientes, $totalRechazadas,
                $tiempoPromedio, $porcentajeCumplimiento,
                'EXCEL', $adminId
            );
        }

        $data = $resultado['data'];
        $adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
        
        require_once __DIR__ . '/../../../app/views/admin/exportar_excel.php';
    }

    public function exportar_pdf(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $tipo = $_GET['tipo'] ?? '';

        $resultado = $this->pqrsModel->obtenerParaExportacion($fechaInicio, $fechaFin, $tipo);
        
        $estados = $resultado['estados'];
        $totalRecibidas = array_sum($estados);
        $totalResueltas = $estados['RESUELTO'] ?? 0;
        $totalPendientes = ($estados['PENDIENTE'] ?? 0) + ($estados['EN_PROCESO'] ?? 0);
        $totalRechazadas = $estados['RECHAZADO'] ?? 0;
        $tiempoPromedio = $resultado['tiempo_promedio'];
        $enTiempo = $resultado['en_tiempo'];
        $porcentajeCumplimiento = $totalResueltas > 0 ? round(($enTiempo / $totalResueltas) * 100, 2) : 0;

        $tipoReporte = !empty($tipo) ? strtoupper($tipo) : 'GENERAL';
        $adminId = $_SESSION['admin_id'] ?? null;

        if ($adminId) {
            $this->pqrsModel->registrarReporte(
                $tipoReporte, $fechaInicio, $fechaFin,
                $totalRecibidas, $totalResueltas, $totalPendientes, $totalRechazadas,
                $tiempoPromedio, $porcentajeCumplimiento,
                'PDF', $adminId
            );
        }

        $data = $resultado['data'];
        $adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
        
        require_once __DIR__ . '/../../../app/views/admin/exportar_pdf.php';
    }
}
