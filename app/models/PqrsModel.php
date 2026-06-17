<?php
require_once __DIR__ . '/../../config/conexion.php';

class PqrsModel {
    private $con;

    public function __construct() {
        $this->con = conexion();
    }

    public function crear($tipo_solicitud, $asunto, $descripcion, $usuario_id) {
        $codigo_radicado = $this->generarCodigoRadicado();
        
        // Calcular fecha de vencimiento (15 días hábiles por defecto, 10 para denuncias)
        $dias = ($tipo_solicitud === 'denuncia') ? 10 : 15;
        // Lógica simplificada de días hábiles, asumiendo días calendario para simplificar o usando DATE_ADD
        $query = "INSERT INTO pqrs (codigo_radicado, tipo_solicitud, asunto, descripcion, usuario_id, estado, fecha_vencimiento) 
                  VALUES (?, ?, ?, ?, ?, 'PENDIENTE', DATE_ADD(CURDATE(), INTERVAL ? DAY))";
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("ssssii", $codigo_radicado, $tipo_solicitud, $asunto, $descripcion, $usuario_id, $dias);
        
        if ($stmt->execute()) {
            return $codigo_radicado;
        }
        return false;
    }

    public function obtenerPorCodigo($codigo) {
        $query = "SELECT p.*, u.nombre_completo, u.correo_electronico, u.tipo_persona 
                  FROM pqrs p 
                  LEFT JOIN usuario u ON p.usuario_id = u.id 
                  WHERE p.codigo_radicado = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function obtenerTodos($limit, $offset, $filtros = []) {
        $where = "1=1";
        $params = [];
        $types = "";

        // Aplicar filtros aquí...
        
        $query = "SELECT p.*, u.nombre_completo 
                  FROM pqrs p 
                  LEFT JOIN usuario u ON p.usuario_id = u.id 
                  WHERE $where 
                  ORDER BY p.fecha_radicacion DESC 
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->con->prepare($query);
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function contarTodos() {
        $query = "SELECT COUNT(*) as total FROM pqrs";
        $result = $this->con->query($query);
        return $result->fetch_assoc()['total'];
    }

    private function generarCodigoRadicado() {
        return 'PQRS-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
