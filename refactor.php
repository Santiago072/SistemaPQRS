<?php
// Script para extraer la lógica de formulario.php al controlador PqrsController.php
$formularioPath = __DIR__ . '/app/views/pqrs/formulario.php';
$controllerPath = __DIR__ . '/app/controllers/PqrsController.php';

$content = file_get_contents($formularioPath);

// Separar la lógica PHP de la vista HTML
$parts = explode('<!DOCTYPE html>', $content);

$phpLogic = trim($parts[0]);
$htmlView = '<!DOCTYPE html>' . "\n" . $parts[1];

// Modificar PqrsController.php
$controllerCode = "<?php
class PqrsController {
    public function formulario() {
        \$tipoPQRS = isset(\$_GET['tipo_pqrs']) ? htmlspecialchars(\$_GET['tipo_pqrs']) : 'peticion';
        \$nombresTipos = [
            'peticion'  => 'Petición',
            'queja'     => 'Queja',
            'reclamo'   => 'Reclamo',
            'sugerencia'=> 'Sugerencia',
            'denuncia'  => 'Denuncia'
        ];
        \$nombreTipo = \$nombresTipos[\$tipoPQRS] ?? 'Petición';
        require_once __DIR__ . '/../views/pqrs/formulario.php';
    }

    public function radicar() {
        // La logica copiada se ejecuta aqui
        // Reemplazar header('Location: confirmacion.php?id=\$pqrs_id') por header('Location: '.BASE_PATH.'index.php?ruta=pqrs/confirmacion&id=\$pqrs_id')
        " . str_replace("<?php", "", $phpLogic) . "
    }

    public function confirmacion() {
        \$id = \$_GET['id'] ?? '';
        require_once __DIR__ . '/../views/pqrs/confirmacion.php';
    }
}
";

// Arreglar rutas en el controllerCode
$controllerCode = str_replace(
    "require_once '../config/conexion.php';", 
    "require_once __DIR__ . '/../../config/conexion.php';", 
    $controllerCode
);
$controllerCode = str_replace(
    "require_once __DIR__ . '/../vendor/autoload.php';", 
    "require_once __DIR__ . '/../../vendor/autoload.php';", 
    $controllerCode
);
$controllerCode = str_replace(
    "require __DIR__ . '/../config/email_config.php';", 
    "require __DIR__ . '/../../config/email_config.php';", 
    $controllerCode
);
$controllerCode = str_replace(
    "__DIR__ . '/../logs/email_log.txt'", 
    "__DIR__ . '/../../logs/email_log.txt'", 
    $controllerCode
);
$controllerCode = str_replace(
    "dirname(__DIR__) . '/uploads'", 
    "dirname(__DIR__, 2) . '/uploads'", 
    $controllerCode
);
$controllerCode = str_replace(
    "header(\"Location: confirmacion.php?id=\$pqrs_id\");", 
    "header(\"Location: \" . BASE_PATH . \"index.php?ruta=pqrs/confirmacion&id=\$pqrs_id\");", 
    $controllerCode
);
$controllerCode = str_replace(
    "action=\"formulario.php\"", 
    "action=\"<?php echo BASE_PATH; ?>index.php?ruta=pqrs/radicar\"", 
    $controllerCode
);


file_put_contents($controllerPath, $controllerCode);

// Modificar formulario.php (solo la vista)
// Quitar el bloque PHP inicial y ajustar enlaces
$htmlView = str_replace(
    "action=\"formulario.php\"", 
    "action=\"<?php echo BASE_PATH; ?>index.php?ruta=pqrs/radicar\"", 
    $htmlView
);
$htmlView = str_replace(
    "href=\"tipos.php\"", 
    "href=\"<?php echo BASE_PATH; ?>index.php?ruta=pqrs/tipos\"", 
    $htmlView
);
$htmlView = str_replace(
    "../css/estilos.css", 
    "<?php echo BASE_PATH; ?>public/css/estilos.css", 
    $htmlView
);

file_put_contents($formularioPath, $htmlView);
echo "Separacion MVC completada para formulario.php";
