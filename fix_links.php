<?php
$dir = new RecursiveDirectoryIterator('app/views/');
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if ($file->getExtension() == 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        // Admin routes without params
        $routes = ['pqrs', 'alertas', 'reportes', 'configuracion', 'login'];
        foreach ($routes as $route) {
            $content = preg_replace('/href=[\"\']' . $route . '\.php[\"\']/', 'href="<?php echo BASE_PATH; ?>index.php?ruta=admin/' . $route . '"', $content);
        }
        
        // Admin routes with params (like pqrs_ver.php?id=...)
        $routesParams = ['pqrs_ver', 'pqrs_responder', 'pqrs_historial'];
        foreach ($routesParams as $route) {
            $content = preg_replace('/href=[\"\']' . $route . '\.php\?([^\"\']*)[\"\']/', 'href="<?php echo BASE_PATH; ?>index.php?ruta=admin/' . $route . '&$1"', $content);
        }
        
        // Export routes
        $content = preg_replace('/href=[\"\']exportar_pdf\.php\?([^\"\']*)[\"\']/', 'href="<?php echo BASE_PATH; ?>index.php?ruta=admin/exportar_pdf&$1"', $content);
        $content = preg_replace('/href=[\"\']exportar_excel\.php\?([^\"\']*)[\"\']/', 'href="<?php echo BASE_PATH; ?>index.php?ruta=admin/exportar_excel&$1"', $content);
        
        // Frontend fixes
        $content = preg_replace('/href=[\"\']consulta_pqrs\.php[\"\']/', 'href="<?php echo BASE_PATH; ?>index.php?ruta=pqrs/consulta"', $content);
        $content = preg_replace('/href=[\"\']terminos\.php[\"\']/', 'href="#"', $content);
        $content = preg_replace('/href=[\"\']consultar\.php[\"\']/', 'href="<?php echo BASE_PATH; ?>index.php?ruta=pqrs/consulta"', $content);
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo 'Fixed: ' . $file->getPathname() . "\n";
        }
    }
}
