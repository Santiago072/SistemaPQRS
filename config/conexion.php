<?php
function conexion() {
    $host = 'mysql.railway.internal';
    $user = 'root';
    $pass = 'WVXMFjQXkasneOrwxSgOESFYaYKhpBYz';
    $db = 'railway';

    $conexion = mysqli_connect($host, $user, $pass, $db);
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $conexion;
}
/* function conexion() {
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db = 'sistema_pqrs';

    $conexion = mysqli_connect($host, $user, $pass, $db);
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    return $conexion;
}
?> */