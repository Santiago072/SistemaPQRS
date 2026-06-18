<?php
/**
 * Database.php — Conexión PDO Singleton
 *
 * Principio: SRP - esta clase tiene UNA responsabilidad: gestionar la conexión a la BD.
 * Principio: Open/Closed - la configuración puede cambiarse sin modificar la clase.
 */

namespace App\Models;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    // Evitar instanciación directa y clonación (patrón Singleton)
    private function __construct() {}
    private function __clone() {}

    /**
     * Devuelve la instancia única de PDO.
     * Si no existe, la crea usando la configuración del entorno.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host = 'localhost';
            $db   = 'sistema_pqrs';
            $user = 'root';
            $pass = '';

            try {
                self::$instance = new PDO(
                    "mysql:host={$host};dbname={$db};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                // En producción, nunca exponer detalles del error al usuario
                error_log('DB Connection Error: ' . $e->getMessage());
                http_response_code(503);
                die('Error: el servicio no esta disponible en este momento. Intente mas tarde.');
            }
        }

        return self::$instance;
    }
}
