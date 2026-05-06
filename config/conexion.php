<?php
function conexion(){
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "sistema_pqrs";

    $conexion = mysqli_connect($host, $user, $pass, $db);
    return $conexion;
}
?>