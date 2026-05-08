<?php
function conexion(){
    $host = "turntable.proxy.rlwy.net";
    $user = "root";
    $pass = "YpKcDtbHqHcfaQwq1nxVNHiQq0brrYgk";
    $db   = "railway";
    $port = 52251;

    // Inicializar con SSL
    $conexion = mysqli_init();
    mysqli_ssl_set($conexion, NULL, NULL, NULL, NULL, NULL);
    
    if (!mysqli_real_connect($conexion, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    
    return $conexion;
}
?>