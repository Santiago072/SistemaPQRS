<?php
// Calcular la ruta relativa desde cualquier archivo hasta la raíz del proyecto
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$depth = substr_count($scriptDir, '/') - 1; // -1 porque empieza con /
$basePath = str_repeat('../', max(0, $depth));
if (empty($basePath)) $basePath = './';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/estilos.css">
</head>
<body>