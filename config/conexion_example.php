<?php
/**
 * ARCHIVO DE EJEMPLO — Conexión a la Base de Datos
 *
 * INSTRUCCIONES:
 * 1. Copia este archivo y renómbralo como: config/conexion.php
 * 2. Completa los valores con tus credenciales reales
 * 3. NUNCA subas config/conexion.php al repositorio (ya está en .gitignore)
 */

function conexion() {
    $host = 'localhost';          // Host de la BD (ej: localhost, 127.0.0.1)
    $user = 'tu_usuario';         // Usuario de MySQL (ej: root)
    $pass = 'tu_contraseña';      // Contraseña de MySQL (vacío en XAMPP por defecto)
    $db   = 'sistema_pqrs';       // Nombre de la base de datos

    $conexion = mysqli_connect($host, $user, $pass, $db);
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    mysqli_set_charset($conexion, 'utf8mb4');
    return $conexion;
}
