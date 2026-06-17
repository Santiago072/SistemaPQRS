<?php
$content = file_get_contents('temp_form_view.php');
// UTF-16LE conversion since PowerShell `>` uses it
$content = mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');

// Extract everything after the last <!DOCTYPE html>
$pos = strrpos($content, '<!DOCTYPE html>');
$htmlView = substr($content, $pos);

// Adjust paths
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
$htmlView = str_replace(
    "src=\"../includes/script.js\"", 
    "src=\"<?php echo BASE_PATH; ?>public/js/script.js\"", 
    $htmlView
);

file_put_contents('app/views/pqrs/formulario.php', $htmlView);

// Fix tipos.php
$tipos = file_get_contents('app/views/pqrs/tipos.php');
$tipos = str_replace("action=\"formulario.php\"", "action=\"<?php echo BASE_PATH; ?>index.php\"", $tipos);
$tipos = str_replace("id=\"tipo_pqrs\" name=\"tipo_pqrs\"", "id=\"tipo_pqrs\" name=\"tipo_pqrs\"\n            <input type=\"hidden\" name=\"ruta\" value=\"pqrs/formulario\">", $tipos);
$tipos = str_replace("href=\"../index.php\"", "href=\"<?php echo BASE_PATH; ?>index.php\"", $tipos);
$tipos = str_replace("href=\"../css/estilos.css\"", "href=\"<?php echo BASE_PATH; ?>public/css/estilos.css\"", $tipos);
file_put_contents('app/views/pqrs/tipos.php', $tipos);

// Fix modal_terminos.php
$modal = file_get_contents('app/views/layouts/modal_terminos.php');
$modal = str_replace("window.location.href = 'pqrs/tipos.php';", "window.location.href = '<?php echo BASE_PATH; ?>index.php?ruta=pqrs/tipos';", $modal);
file_put_contents('app/views/layouts/modal_terminos.php', $modal);

echo "View fixes applied.";
