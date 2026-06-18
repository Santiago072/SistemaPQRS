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
}
