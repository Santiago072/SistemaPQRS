<?php
function conexion(){
    $host = getenv('DB_HOST') ?: "turntable.proxy.rlwy.net";
    $user = getenv('DB_USER') ?: "root";
    $pass = getenv('DB_PASS') ?: "YpKcDtbHqHcfaQwq1nxVNHiQq0brrYgk";
    $db   = getenv('DB_NAME') ?: "railway";
    $port = getenv('DB_PORT') ?: "52251";

    $conexion = mysqli_connect($host, $user, $pass, $db, (int)$port);

    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    
    return $conexion;
}
?>