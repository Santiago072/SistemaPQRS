<?php
/**
 * UsuarioModel.php — Modelo para operaciones de ciudadanos
 *
 * Principio: SRP - solo gestiona datos de la tabla `usuario`.
 * Principio: DIP - depende de la abstracción PDO, no de MySQLi.
 */

namespace App\Models;

use PDO;

class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Inserta un nuevo ciudadano y retorna su ID.
     */
    public function crear(array $datos): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO usuario (
                tipo_persona, nombre_completo, documento_identidad, tipo_documento,
                correo_electronico, telefono, razon_social, nit,
                nombre_representante, correo_corporativo
            ) VALUES (
                :tipo_persona, :nombre_completo, :documento_identidad, :tipo_documento,
                :correo_electronico, :telefono, :razon_social, :nit,
                :nombre_representante, :correo_corporativo
            )"
        );

        $stmt->execute([
            ':tipo_persona'        => $datos['tipo_persona']        ?? null,
            ':nombre_completo'     => $datos['nombre_completo']     ?? null,
            ':documento_identidad' => $datos['documento_identidad'] ?? null,
            ':tipo_documento'      => $datos['tipo_documento']      ?? null,
            ':correo_electronico'  => $datos['correo_electronico']  ?? null,
            ':telefono'            => $datos['telefono']            ?? null,
            ':razon_social'        => $datos['razon_social']        ?? null,
            ':nit'                 => $datos['nit']                 ?? null,
            ':nombre_representante'=> $datos['nombre_representante']?? null,
            ':correo_corporativo'  => $datos['correo_corporativo']  ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Obtiene un usuario por su ID.
     */
    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
