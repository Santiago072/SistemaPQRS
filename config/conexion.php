<?php
function conexion(){
    $host = "sql10.freesqldatabase.com";
    $user = "sql10825860";
    $pass = "6rsS9peEn3";
    $db = "sql10825860";

    $conexion = mysqli_connect($host, $user, $pass, $db);
    return $conexion;
}
?>