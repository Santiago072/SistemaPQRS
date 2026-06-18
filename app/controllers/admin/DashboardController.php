<?php
namespace App\Controllers\Admin;

use App\Models\PqrsModel;

class DashboardController
{
    private PqrsModel $pqrsModel;

    public function __construct(PqrsModel $pqrsModel)
    {
        $this->pqrsModel = $pqrsModel;
    }

    public function dashboard(): void          
    { 
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        if (!$adminId) {
            header('Location: ' . BASE_PATH . 'index.php?ruta=admin/login');
            exit();
        }

        $adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';
        $adminRol = $_SESSION['admin_rol'] ?? 'admin';

        $stats = $this->pqrsModel->obtenerEstadisticasDashboard();
        $ultimasPQRS = $stats['ultimas'];

        require_once __DIR__ . '/../../../app/views/admin/dashboard_admin.php'; 
    }
}
