<?php
/**
 * PqrsModel.php — Modelo para operaciones de la tabla `pqrs` e historial
 *
 * Principio: SRP - solo gestiona datos de PQRS.
 * Principio: DIP - depende de PDO (abstracción), no de MySQLi.
 * Principio: OCP - se pueden agregar métodos sin modificar los existentes.
 */

namespace App\Models;

use PDO;

class PqrsModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── Generación de código radicado ────────────────────────────────────────

    public function generarCodigoRadicado(): string
    {
        $anio = date('Y');
        $mes  = date('m');
        $stmt = $this->db->prepare(
            "SELECT MAX(CAST(SUBSTRING(codigo_radicado, -3) AS UNSIGNED)) AS max_num
             FROM pqrs
             WHERE YEAR(fecha_radicacion) = :anio
               AND MONTH(fecha_radicacion) = :mes"
        );
        $stmt->execute([':anio' => $anio, ':mes' => $mes]);
        $row         = $stmt->fetch();
        $maxNum      = $row['max_num'] ?? 0;
        $consecutivo = str_pad(($maxNum + 1), 3, '0', STR_PAD_LEFT);
        return "PQRS-{$anio}-{$mes}-{$consecutivo}";
    }

    // ─── Obtener días de vencimiento desde configuración ─────────────────────

    public function obtenerDiasVencimiento(string $tipoPqrs): int
    {
        $stmt = $this->db->prepare(
            "SELECT dias_vencimiento_{$tipoPqrs} AS dias
             FROM configuracion_sistema WHERE id = 1"
        );
        $stmt->execute();
        $row = $stmt->fetch();
        return (int) ($row['dias'] ?? 15);
    }

    // ─── Crear nueva PQRS ─────────────────────────────────────────────────────

    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO pqrs (
                codigo_radicado, tipo_solicitud, asunto, descripcion,
                archivo_adjunto, estado, fecha_vencimiento,
                desea_notificacion, usuario_id, administrador_id
            ) VALUES (
                :codigo_radicado, :tipo_solicitud, :asunto, :descripcion,
                :archivo_adjunto, :estado, :fecha_vencimiento,
                :desea_notificacion, :usuario_id, :administrador_id
            )"
        );

        $stmt->execute([
            ':codigo_radicado'    => $datos['codigo_radicado'],
            ':tipo_solicitud'     => $datos['tipo_solicitud'],
            ':asunto'             => $datos['asunto'],
            ':descripcion'        => $datos['descripcion'],
            ':archivo_adjunto'    => $datos['archivo_adjunto']    ?? null,
            ':estado'             => $datos['estado']             ?? 'PENDIENTE',
            ':fecha_vencimiento'  => $datos['fecha_vencimiento'],
            ':desea_notificacion' => $datos['desea_notificacion'] ?? 0,
            ':usuario_id'         => $datos['usuario_id'],
            ':administrador_id'   => $datos['administrador_id']   ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    // ─── Consultas ────────────────────────────────────────────────────────────

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.nombre_completo, u.correo_electronico, u.tipo_persona,
                    u.correo_corporativo, u.nombre_representante,
                    DATEDIFF(p.fecha_vencimiento, CURDATE()) AS dias_restantes
             FROM pqrs p
             LEFT JOIN usuario u ON p.usuario_id = u.id
             WHERE p.id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function obtenerPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.nombre_completo, u.correo_electronico, u.tipo_persona,
                    u.correo_corporativo, u.nombre_representante
             FROM pqrs p
             LEFT JOIN usuario u ON p.usuario_id = u.id
             WHERE p.codigo_radicado = :codigo"
        );
        $stmt->execute([':codigo' => $codigo]);
        return $stmt->fetch() ?: null;
    }

    public function obtenerListadoPorCorreo(string $correo): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, u.tipo_persona, u.nombre_completo, u.correo_electronico,
                    u.correo_corporativo, u.nombre_representante
             FROM pqrs p
             LEFT JOIN usuario u ON p.usuario_id = u.id
             WHERE LOWER(u.correo_electronico) = :correo1
                OR LOWER(u.correo_corporativo) = :correo2
             ORDER BY p.fecha_radicacion DESC"
        );
        $correo_lower = strtolower($correo);
        $stmt->execute([':correo1' => $correo_lower, ':correo2' => $correo_lower]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // ─── Actualizar estado ────────────────────────────────────────────────────

    /**
     * Valida y aplica una transición de estado.
     * Retorna true en éxito, false si la transición no es válida.
     */
    public function cambiarEstado(int $id, string $nuevoEstado): bool
    {
        $transicionesValidas = [
            'PENDIENTE'  => ['EN_PROCESO', 'RESUELTO', 'RECHAZADO'],
            'EN_PROCESO' => ['RESUELTO', 'RECHAZADO'],
            'RESUELTO'   => [],
            'RECHAZADO'  => [],
        ];

        $pqrs = $this->obtenerPorId($id);
        if (!$pqrs) {
            return false;
        }

        if (!in_array($nuevoEstado, $transicionesValidas[$pqrs['estado']] ?? [], true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE pqrs SET estado = :estado, fecha_actualizacion = NOW() WHERE id = :id"
        );
        return $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
    }

    // ─── Guardar respuesta del administrador ──────────────────────────────────

    public function guardarRespuesta(int $id, string $contenido, int $adminId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE pqrs
             SET respuesta_administrador = :contenido,
                 fecha_respuesta = NOW(),
                 administrador_id = :admin_id
             WHERE id = :id"
        );
        return $stmt->execute([
            ':contenido' => $contenido,
            ':admin_id'  => $adminId,
            ':id'        => $id,
        ]);
    }

    // ─── Historial de acciones ────────────────────────────────────────────────

    public function registrarAccion(
        int    $pqrsId,
        int    $adminId,
        string $accion,
        string $descripcion,
        string $estadoAnterior = '',
        string $estadoNuevo = ''
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO historial_accion
                (pqrs_id, administrador_id, accion_realizada, estado_anterior, estado_nuevo, descripcion, fecha_hora)
             VALUES
                (:pqrs_id, :admin_id, :accion, :estado_anterior, :estado_nuevo, :descripcion, NOW())"
        );
        $stmt->execute([
            ':pqrs_id'        => $pqrsId,
            ':admin_id'       => $adminId,
            ':accion'         => $accion,
            ':estado_anterior'=> $estadoAnterior,
            ':estado_nuevo'   => $estadoNuevo,
            ':descripcion'    => $descripcion,
        ]);
    }

    // ─── Historial de una PQRS ────────────────────────────────────────────────

    public function obtenerHistorial(int $pqrsId): array
    {
        $stmt = $this->db->prepare(
            "SELECT h.*, a.nombre AS admin_nombre
             FROM historial_accion h
             LEFT JOIN administrador a ON h.administrador_id = a.id
             WHERE h.pqrs_id = :pqrs_id
             ORDER BY h.fecha_hora DESC"
        );
        $stmt->execute([':pqrs_id' => $pqrsId]);
        return $stmt->fetchAll();
    }
    // ─── Estadísticas de Dashboard ────────────────────────────────────────────

    public function obtenerEstadisticasDashboard(): array
    {
        $stats = [
            'total'      => 0,
            'mes_actual' => 0,
            'vencidas'   => 0,
            'por_estado' => [],
            'alertas'    => ['critico' => 0, 'urgente' => 0, 'moderado' => 0],
            'ultimas'    => []
        ];

        // Total
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM pqrs");
        $stats['total'] = (int) $stmt->fetchColumn();

        // Por estado
        $stmt = $this->db->query("SELECT estado, COUNT(*) as cantidad FROM pqrs GROUP BY estado");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats['por_estado'][$row['estado']] = (int) $row['cantidad'];
        }

        // Mes actual
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM pqrs WHERE MONTH(fecha_radicacion) = MONTH(CURRENT_DATE()) AND YEAR(fecha_radicacion) = YEAR(CURRENT_DATE())");
        $stats['mes_actual'] = (int) $stmt->fetchColumn();

        // Vencidas
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM pqrs WHERE fecha_vencimiento < CURDATE() AND estado IN ('PENDIENTE', 'EN_PROCESO')");
        $stats['vencidas'] = (int) $stmt->fetchColumn();

        // Alertas
        $stmt = $this->db->query("SELECT 
            SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 5 AND DATEDIFF(fecha_vencimiento, CURDATE()) >= 0 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as critico,
            SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 6 AND 10 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as urgente,
            SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 11 AND 15 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as moderado
            FROM pqrs WHERE fecha_vencimiento IS NOT NULL");
        $alertas = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($alertas) {
            $stats['alertas'] = [
                'critico'  => (int) $alertas['critico'],
                'urgente'  => (int) $alertas['urgente'],
                'moderado' => (int) $alertas['moderado']
            ];
        }

        // Últimas 5
        $stmt = $this->db->query("SELECT p.*, u.nombre_completo, u.correo_electronico 
                                  FROM pqrs p 
                                  LEFT JOIN usuario u ON p.usuario_id = u.id 
                                  ORDER BY p.fecha_radicacion DESC LIMIT 5");
        $stats['ultimas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }

    public function obtenerReportesEstadisticas(string $fechaInicio, string $fechaFin, string $tipo): array
    {
        $metricas = [
            'total_recibidas' => 0,
            'por_estado'      => [],
            'por_tipo'        => [],
            'tiempo_promedio' => 0,
            'en_tiempo'       => 0,
            'fuera_tiempo'    => 0,
            'por_mes'         => []
        ];

        $whereConditions = ["DATE(p.fecha_radicacion) BETWEEN :fecha_inicio AND :fecha_fin"];
        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin
        ];

        if (!empty($tipo)) {
            $whereConditions[] = "p.tipo_solicitud = :tipo";
            $params[':tipo'] = $tipo;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

        // Total
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM pqrs p $whereClause");
        $stmt->execute($params);
        $metricas['total_recibidas'] = (int) $stmt->fetchColumn();

        // Por estado
        $stmt = $this->db->prepare("SELECT estado, COUNT(*) as cantidad FROM pqrs p $whereClause GROUP BY estado");
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $metricas['por_estado'][$row['estado']] = (int) $row['cantidad'];
        }

        // Por tipo
        $stmt = $this->db->prepare("SELECT tipo_solicitud, COUNT(*) as cantidad FROM pqrs p $whereClause GROUP BY tipo_solicitud ORDER BY cantidad DESC");
        $stmt->execute($params);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $metricas['por_tipo'][$row['tipo_solicitud']] = (int) $row['cantidad'];
        }

        // Tiempo promedio
        $stmt = $this->db->prepare("SELECT AVG(DATEDIFF(COALESCE(p.fecha_respuesta, p.fecha_actualizacion), p.fecha_radicacion)) as promedio FROM pqrs p $whereClause AND p.estado = 'RESUELTO'");
        $stmt->execute($params);
        $metricas['tiempo_promedio'] = round((float) $stmt->fetchColumn(), 1);

        // En términos vs fuera
        $stmt = $this->db->prepare("SELECT 
            SUM(CASE WHEN p.fecha_actualizacion <= p.fecha_vencimiento THEN 1 ELSE 0 END) as en_tiempo,
            SUM(CASE WHEN p.fecha_actualizacion > p.fecha_vencimiento THEN 1 ELSE 0 END) as fuera_tiempo
            FROM pqrs p $whereClause AND p.estado = 'RESUELTO' AND p.fecha_vencimiento IS NOT NULL");
        $stmt->execute($params);
        $terminos = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($terminos) {
            $metricas['en_tiempo'] = (int) $terminos['en_tiempo'];
            $metricas['fuera_tiempo'] = (int) $terminos['fuera_tiempo'];
        }

        // Por mes (últimos 6 meses) no usa whereClause completo de filtros de fecha para poder ver tendencia
        $stmt = $this->db->query("SELECT DATE_FORMAT(p.fecha_radicacion, '%Y-%m') as mes, 
                 COUNT(*) as total,
                 SUM(CASE WHEN estado = 'RESUELTO' THEN 1 ELSE 0 END) as resueltas
          FROM pqrs p 
          WHERE p.fecha_radicacion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
          GROUP BY DATE_FORMAT(p.fecha_radicacion, '%Y-%m')
          ORDER BY mes ASC");
        $metricas['por_mes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $metricas;
    }

    public function obtenerListadoPaginado(array $filtros, int $pagina, int $porPagina): array
    {
        $offset = ($pagina - 1) * $porPagina;
        $whereConditions = [];
        $params = [];

        if (!empty($filtros['estado'])) {
            $whereConditions[] = "p.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['tipo'])) {
            $whereConditions[] = "p.tipo_solicitud = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['fecha_inicio'])) {
            $whereConditions[] = "DATE(p.fecha_radicacion) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $whereConditions[] = "DATE(p.fecha_radicacion) <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['busqueda'])) {
            $whereConditions[] = "(p.codigo_radicado LIKE :b1 OR p.asunto LIKE :b2 OR u.nombre_completo LIKE :b3)";
            $params[':b1'] = '%' . $filtros['busqueda'] . '%';
            $params[':b2'] = '%' . $filtros['busqueda'] . '%';
            $params[':b3'] = '%' . $filtros['busqueda'] . '%';
        }

        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // Order
        $orden = $filtros['orden'] ?? 'recientes';
        $orderClause = match($orden) {
            'antiguos' => 'ORDER BY p.fecha_radicacion ASC',
            'vencimiento' => 'ORDER BY p.fecha_vencimiento ASC',
            'codigo' => 'ORDER BY p.codigo_radicado ASC',
            default => 'ORDER BY p.fecha_radicacion DESC'
        };

        // Total registros
        $countQuery = "SELECT COUNT(*) as total FROM pqrs p LEFT JOIN usuario u ON p.usuario_id = u.id $whereClause";
        $stmtCount = $this->db->prepare($countQuery);
        $stmtCount->execute($params);
        $totalRegistros = (int) $stmtCount->fetchColumn();

        // Data
        $query = "SELECT p.*, u.nombre_completo, u.tipo_persona, u.correo_electronico,
                         DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
                  FROM pqrs p 
                  LEFT JOIN usuario u ON p.usuario_id = u.id 
                  $whereClause 
                  $orderClause 
                  LIMIT $porPagina OFFSET $offset";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $pqrsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats
        $statsStmt = $this->db->query("SELECT estado, COUNT(*) as cantidad FROM pqrs GROUP BY estado");
        $estadisticas = [];
        while ($row = $statsStmt->fetch(PDO::FETCH_ASSOC)) {
            $estadisticas[$row['estado']] = (int) $row['cantidad'];
        }

        return [
            'total_registros' => $totalRegistros,
            'total_paginas'   => ceil($totalRegistros / $porPagina),
            'data'            => $pqrsList,
            'estadisticas'    => $estadisticas
        ];
    }

    public function obtenerAlertasVencimiento(): array
    {
        $query = "SELECT 
            SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) <= 5 AND DATEDIFF(fecha_vencimiento, CURDATE()) >= 0 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as critico,
            SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 6 AND 10 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as urgente,
            SUM(CASE WHEN DATEDIFF(fecha_vencimiento, CURDATE()) BETWEEN 11 AND 15 AND estado IN ('PENDIENTE', 'EN_PROCESO') THEN 1 ELSE 0 END) as moderado
            FROM pqrs";
        $stmt = $this->db->query($query);
        $alertas = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'critico'  => (int) ($alertas['critico'] ?? 0),
            'urgente'  => (int) ($alertas['urgente'] ?? 0),
            'moderado' => (int) ($alertas['moderado'] ?? 0)
        ];
    }

    public function obtenerAlertasDetalladas(): array
    {
        $query = "SELECT p.*, u.nombre_completo, u.correo_electronico,
                         DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
                  FROM pqrs p 
                  LEFT JOIN usuario u ON p.usuario_id = u.id 
                  WHERE p.estado IN ('PENDIENTE', 'EN_PROCESO')
                  AND p.fecha_vencimiento IS NOT NULL
                  ORDER BY p.fecha_vencimiento ASC";
        $stmt = $this->db->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $alertas_critico = [];   // 0-5 días
        $alertas_urgente = [];   // 6-10 días
        $alertas_moderado = [];  // 11-15 días
        $alertas_vencidas = [];  // Vencidas

        foreach ($result as $row) {
            $dias = (int) $row['dias_restantes'];
            if ($dias < 0) {
                $alertas_vencidas[] = $row;
            } elseif ($dias <= 5) {
                $alertas_critico[] = $row;
            } elseif ($dias <= 10) {
                $alertas_urgente[] = $row;
            } elseif ($dias <= 15) {
                $alertas_moderado[] = $row;
            }
        }

        return [
            'critico' => $alertas_critico,
            'urgente' => $alertas_urgente,
            'moderado' => $alertas_moderado,
            'vencidas' => $alertas_vencidas
        ];
    }

    public function obtenerDetalleCompleto(int $id): ?array
    {
        $query = "SELECT p.*, u.nombre_completo, u.tipo_persona, u.tipo_documento, u.documento_identidad,
                         u.correo_electronico, u.telefono,
                         u.razon_social, u.nit, u.nombre_representante, u.correo_corporativo,
                         DATEDIFF(p.fecha_vencimiento, CURDATE()) as dias_restantes
                  FROM pqrs p 
                  LEFT JOIN usuario u ON p.usuario_id = u.id 
                  WHERE p.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function obtenerHistorialAcciones(int $pqrsId): array
    {
        $query = "SELECT h.*, a.nombre_completo as admin_nombre 
                  FROM historial_accion h 
                  LEFT JOIN administrador a ON h.administrador_id = a.id 
                  WHERE h.pqrs_id = :pqrs_id 
                  ORDER BY h.fecha_hora DESC 
                  LIMIT 10";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':pqrs_id' => $pqrsId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerParaExportacion(string $fechaInicio, string $fechaFin, string $tipo): array
    {
        $whereConditions = ["DATE(p.fecha_radicacion) BETWEEN :fecha_inicio AND :fecha_fin"];
        $params = [
            ':fecha_inicio' => $fechaInicio,
            ':fecha_fin'    => $fechaFin
        ];

        if (!empty($tipo)) {
            $whereConditions[] = "p.tipo_solicitud = :tipo";
            $params[':tipo'] = $tipo;
        }

        $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);

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
                  $whereClause 
                  ORDER BY p.fecha_radicacion DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats
        $statsStmt = $this->db->prepare("SELECT estado, COUNT(*) as cantidad FROM pqrs p $whereClause GROUP BY estado");
        $statsStmt->execute($params);
        $estados = [];
        while ($r = $statsStmt->fetch(PDO::FETCH_ASSOC)) {
            $estados[$r['estado']] = (int)$r['cantidad'];
        }

        // Tiempo Promedio
        $tpStmt = $this->db->prepare("SELECT AVG(DATEDIFF(COALESCE(p.fecha_respuesta, p.fecha_actualizacion), p.fecha_radicacion)) as promedio FROM pqrs p $whereClause AND p.estado = 'RESUELTO'");
        $tpStmt->execute($params);
        $tiempoPromedio = round((float) $tpStmt->fetchColumn(), 1);

        // En tiempo
        $etStmt = $this->db->prepare("SELECT SUM(CASE WHEN p.fecha_actualizacion <= p.fecha_vencimiento THEN 1 ELSE 0 END) as en_tiempo FROM pqrs p $whereClause AND p.estado = 'RESUELTO' AND p.fecha_vencimiento IS NOT NULL");
        $etStmt->execute($params);
        $enTiempo = (int) $etStmt->fetchColumn();

        return [
            'data' => $data,
            'estados' => $estados,
            'tiempo_promedio' => $tiempoPromedio,
            'en_tiempo' => $enTiempo
        ];
    }

    public function registrarReporte(
        string $tipoReporte,
        string $fechaInicio,
        string $fechaFin,
        int $totalRecibidas,
        int $totalResueltas,
        int $totalPendientes,
        int $totalRechazadas,
        float $tiempoPromedio,
        float $porcentajeCumplimiento,
        string $formatoExportacion,
        int $administradorId
    ): void {
        $query = "INSERT INTO reporte (
                    tipo_reporte, fecha_inicio, fecha_fin,
                    total_recibidas, total_resueltas, total_pendientes, total_rechazadas,
                    tiempo_promedio_respuesta, porcentaje_cumplimiento,
                    formato_exportacion, administrador_id
                  ) VALUES (
                    :tipo_reporte, :fecha_inicio, :fecha_fin,
                    :total_recibidas, :total_resueltas, :total_pendientes, :total_rechazadas,
                    :tiempo_promedio, :porcentaje, :formato, :admin_id
                  )";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':tipo_reporte'    => $tipoReporte,
            ':fecha_inicio'    => $fechaInicio,
            ':fecha_fin'       => $fechaFin,
            ':total_recibidas' => $totalRecibidas,
            ':total_resueltas' => $totalResueltas,
            ':total_pendientes' => $totalPendientes,
            ':total_rechazadas' => $totalRechazadas,
            ':tiempo_promedio' => $tiempoPromedio,
            ':porcentaje'      => $porcentajeCumplimiento,
            ':formato'         => $formatoExportacion,
            ':admin_id'        => $administradorId
        ]);
    }
}
