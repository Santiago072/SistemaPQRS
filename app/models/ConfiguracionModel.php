<?php
namespace App\Models;

use PDO;
use App\Models\Database;

class ConfiguracionModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function obtenerConfiguracion(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM configuracion_sistema WHERE id = 1 LIMIT 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function actualizarConfiguracion(
        int $dias_peticion,
        int $dias_queja,
        int $dias_reclamo,
        int $dias_sugerencia,
        int $dias_denuncia,
        string $correo,
        string $empresa
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE configuracion_sistema 
            SET dias_vencimiento_peticion = :p,
                dias_vencimiento_queja = :q,
                dias_vencimiento_reclamo = :r,
                dias_vencimiento_sugerencia = :s,
                dias_vencimiento_denuncia = :d,
                correo_notificaciones = :correo,
                nombre_empresa = :empresa
            WHERE id = 1
        ");
        return $stmt->execute([
            'p' => $dias_peticion,
            'q' => $dias_queja,
            'r' => $dias_reclamo,
            's' => $dias_sugerencia,
            'd' => $dias_denuncia,
            'correo' => $correo,
            'empresa' => $empresa
        ]);
    }
}
