<?php
function conexion(){
    $host = "turntable.proxy.rlwy.net";
    $user = "appuser"; // Hardcodeado temporalmente
    $pass = "contrasenaprueba123"; // Hardcodeado temporalmente
    $db   = "railway";
    $port = 52251;

    $conexion = mysqli_connect($host, $user, $pass, $db, $port);

    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    
    return $conexion;
}
?>