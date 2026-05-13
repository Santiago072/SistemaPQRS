<?php
function conexion() {
    $host = 'mysql.railway.internal';
    $user = 'root';
    $pass = 'YpKcDtbHqHcfaQwqlnxVNHiQqObrrYgk';
    $db = 'railway';

    $conexion = mysqli_connect($host, $user, $pass, $db);
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $conexion;
}