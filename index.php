<?php
// Requerir el autoloader de Composer para cargar las clases de App\Models, App\Controllers, etc.
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Container;

// Cargar variables de entorno si existe el archivo .env (XAMPP/Local)
require_once __DIR__ . '/config/EnvLoader.php';
\App\Config\EnvLoader::load(__DIR__ . '/.env');

// Configurar base path (Por defecto /SistemaPQRS/ para XAMPP local, o / para VPS)
$appBase = getenv('APP_BASE') ?: '/SistemaPQRS/';
define('BASE_PATH', $appBase);

// Inicializar el contenedor de Inyección de Dependencias
$container = new Container();

// Mapa estricto de rutas
$rutas = [
    // Rutas Públicas (Usuario)
    'home/index' => [\App\Controllers\HomeController::class, 'index'],
    'pqrs/index' => [\App\Controllers\PqrsController::class, 'index'],
    'pqrs/crear' => [\App\Controllers\PqrsController::class, 'crear'],
    'pqrs/consulta' => [\App\Controllers\PqrsController::class, 'consulta'],
    'pqrs/estado' => [\App\Controllers\PqrsController::class, 'estado'],

    // Rutas de Administrador - Auth
    'admin/login' => [\App\Controllers\Admin\AuthController::class, 'login'],
    'admin/logout' => [\App\Controllers\Admin\AuthController::class, 'logout'],
    'admin/recuperar' => [\App\Controllers\Admin\AuthController::class, 'recuperar'],
    'admin/recuperar_contrasena' => [\App\Controllers\Admin\AuthController::class, 'recuperar'],
    'admin/restablecer_contrasena' => [\App\Controllers\Admin\AuthController::class, 'restablecer_contrasena'],
    
    // Rutas de Administrador - Dashboard y Config
    'admin/dashboard' => [\App\Controllers\Admin\DashboardController::class, 'dashboard'],
    'admin/configuracion' => [\App\Controllers\Admin\ConfigController::class, 'configuracion'],
    'admin/actualizar_perfil' => [\App\Controllers\Admin\ConfigController::class, 'actualizar_perfil'],
    
    // Rutas de Administrador - PQRS
    'admin/pqrs' => [\App\Controllers\Admin\PqrsController::class, 'pqrs'],
    'admin/pqrs_ver' => [\App\Controllers\Admin\PqrsController::class, 'pqrs_ver'],
    'admin/pqrs_cambiar_estado' => [\App\Controllers\Admin\PqrsController::class, 'pqrs_cambiar_estado'],
    'admin/pqrs_responder' => [\App\Controllers\Admin\PqrsController::class, 'pqrs_responder'],
    'admin/guardar_respuesta' => [\App\Controllers\Admin\PqrsController::class, 'guardar_respuesta'],
    'admin/pqrs_historial' => [\App\Controllers\Admin\PqrsController::class, 'pqrs_historial'],
    'admin/alertas' => [\App\Controllers\Admin\PqrsController::class, 'alertas'],

    // Rutas de Administrador - Reportes
    'admin/reportes' => [\App\Controllers\Admin\ReportController::class, 'reportes'],
    'admin/exportar_excel' => [\App\Controllers\Admin\ReportController::class, 'exportar_excel'],
    'admin/exportar_pdf' => [\App\Controllers\Admin\ReportController::class, 'exportar_pdf'],
];

// Obtener ruta de la URL
$ruta = $_GET['ruta'] ?? 'home/index';

// Despachar la ruta
if (array_key_exists($ruta, $rutas)) {
    try {
        $controladorClase = $rutas[$ruta][0];
        $metodo = $rutas[$ruta][1];
        
        // El contenedor inyecta automáticamente los Modelos en el Constructor del Controlador
        $controlador = $container->get($controladorClase);
        
        if (method_exists($controlador, $metodo)) {
            $controlador->$metodo();
        } else {
            echo "Error 404: Método no encontrado en el controlador.";
        }
    } catch (Exception $e) {
        echo "Error del Sistema (DIP): " . $e->getMessage();
    }
} else {
    // Fallback: Compatibilidad con controladores antiguos que no están en el mapa
    // Esto es útil por si falta mapear algo
    $partes = explode('/', $ruta);
    $controladorNombre = ucfirst($partes[0]) . 'Controller';
    $metodo = $partes[1] ?? 'index';
    
    // Buscar primero en el namespace raíz de controladores
    $controladorClaseRaiz = "App\\Controllers\\" . $controladorNombre;
    $archivoControlador = __DIR__ . '/app/controllers/' . $controladorNombre . '.php';

    if (file_exists($archivoControlador)) {
        try {
            // Intentar inyección si la clase existe con su namespace
            if (class_exists($controladorClaseRaiz)) {
                $controlador = $container->get($controladorClaseRaiz);
            } else {
                // Fallback sin namespace (legado)
                require_once $archivoControlador;
                $controlador = new $controladorNombre();
            }
            
            if (method_exists($controlador, $metodo)) {
                $controlador->$metodo();
            } else {
                echo "Error 404: Método no encontrado";
            }
        } catch (Exception $e) {
             echo "Error del Sistema (Fallback): " . $e->getMessage();
        }
    } else {
        echo "Error 404: Ruta y Controlador no encontrados: " . htmlspecialchars($ruta);
    }
}