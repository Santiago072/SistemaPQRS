<?php
// Requerir el autoloader de Composer para cargar las clases de App\Models, App\Controllers, etc.
require_once __DIR__ . '/vendor/autoload.php';

// Configurar base path
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isRailway = (strpos($host, 'railway.app') !== false) || (getenv('RAILWAY_ENVIRONMENT') !== false);
define('BASE_PATH', $isRailway ? '/' : '/PROYECTO_PQRS/');

// Enrutador muy básico
$ruta = $_GET['ruta'] ?? 'home/index';
$partes = explode('/', $ruta);

$controladorNombre = ucfirst($partes[0]) . 'Controller';
$metodo = $partes[1] ?? 'index';

$archivoControlador = __DIR__ . '/app/controllers/' . $controladorNombre . '.php';

if (file_exists($archivoControlador)) {
    require_once $archivoControlador;
    $controlador = new $controladorNombre();
    
    if (method_exists($controlador, $metodo)) {
        $controlador->$metodo();
    } else {
        echo "Error 404: Método no encontrado";
    }
} else {
    echo "Error 404: Controlador no encontrado";
}