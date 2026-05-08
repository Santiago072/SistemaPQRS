<?php
function conexion(){
    $host = getenv('MYSQLHOST') ?: "turntable.proxy.rlwy.net";
    $user = getenv('MYSQLUSER') ?: "root";
    $pass = getenv('MYSQLPASSWORD') ?: "YpKcDtbHqHcfaQwq1nxVNHiQq0brrYgk";
    $db = getenv('MYSQLDATABASE') ?: "railway";
    $port = getenv('MYSQLPORT') ?: 52251;

    $conexion = mysqli_connect($host, $user, $pass, $db, $port);

    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    
    return $conexion;
}
?>