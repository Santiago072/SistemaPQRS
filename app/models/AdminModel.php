<?php
namespace App\Models;

use PDO;
use App\Models\Database;

class AdminModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function obtenerPorUsuario(string $usuario): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM administrador WHERE nombre_usuario = :usuario AND estado = 'activo' LIMIT 1");
        $stmt->execute(['usuario' => $usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM administrador WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function obtenerPorCorreo(string $correo): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM administrador WHERE correo_electronico = :correo AND estado = 'activo' LIMIT 1");
        $stmt->execute(['correo' => $correo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function obtenerPorTokenRecuperacion(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM administrador WHERE token_recuperacion = :token AND token_expiracion > NOW() AND estado = 'activo' LIMIT 1");
        $stmt->execute(['token' => $token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function actualizarTokenRecuperacion(int $id, ?string $token, ?string $expiracion): bool
    {
        $stmt = $this->db->prepare("UPDATE administrador SET token_recuperacion = :token, token_expiracion = :expiracion WHERE id = :id");
        return $stmt->execute([
            'token' => $token,
            'expiracion' => $expiracion,
            'id' => $id
        ]);
    }

    public function actualizarContrasena(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare("UPDATE administrador SET contrasena = :hash WHERE id = :id");
        return $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    public function actualizarPerfil(int $id, string $nombre, string $correo, ?string $hash = null): bool
    {
        if ($hash) {
            $stmt = $this->db->prepare("UPDATE administrador SET nombre_completo = :nombre, correo_electronico = :correo, contrasena = :hash WHERE id = :id");
            return $stmt->execute(['nombre' => $nombre, 'correo' => $correo, 'hash' => $hash, 'id' => $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE administrador SET nombre_completo = :nombre, correo_electronico = :correo WHERE id = :id");
            return $stmt->execute(['nombre' => $nombre, 'correo' => $correo, 'id' => $id]);
        }
    }
}
