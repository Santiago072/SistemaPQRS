<?php
function conexion(){
    $host = getenv('DB_HOST') ?: "turntable.proxy.rlwy.net";
    $user = getenv('DB_USER') ?: "root";
    $pass = getenv('DB_PASS') ?: "YpKcDtbHqHcfaQwq1nxVNHiQq0brrYgk";
    $db   = getenv('DB_NAME') ?: "railway";
    $port = getenv('DB_PORT') ?: "52251";

    // Inicializar conexión
    $conexion = mysqli_init();
    
    // Configurar SSL (requerido para caching_sha2_password)
    mysqli_ssl_set($conexion, NULL, NULL, NULL, NULL, NULL);
    
    // Conectar con flag SSL
    if (!mysqli_real_connect($conexion, $host, $user, $pass, $db, (int)$port, NULL, MYSQLI_CLIENT_SSL)) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    
    return $conexion;
}
?>