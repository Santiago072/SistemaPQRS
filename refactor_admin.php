<?php
$adminFiles = glob(__DIR__ . '/administrador/*.php');
$viewsAdminDir = __DIR__ . '/app/views/admin/';

foreach ($adminFiles as $file) {
    $basename = basename($file);
    copy($file, $viewsAdminDir . $basename);
    
    // Read view content and fix paths
    $content = file_get_contents($viewsAdminDir . $basename);
    
    // Fix include paths
    $content = str_replace("include '../includes/", "include __DIR__ . '/../layouts/", $content);
    $content = str_replace("require_once '../config/", "require_once __DIR__ . '/../../../config/", $content);
    $content = str_replace("include '../config/", "include __DIR__ . '/../../../config/", $content);
    
    // Fix links to index.php router
    $content = str_replace("href=\"login.php\"", "href=\"<?php echo BASE_PATH; ?>index.php?ruta=admin/login\"", $content);
    $content = str_replace("action=\"login.php\"", "action=\"<?php echo BASE_PATH; ?>index.php?ruta=admin/login\"", $content);
    $content = str_replace("href=\"dashboard_admin.php\"", "href=\"<?php echo BASE_PATH; ?>index.php?ruta=admin/dashboard\"", $content);
    $content = str_replace("href=\"logout.php\"", "href=\"<?php echo BASE_PATH; ?>index.php?ruta=admin/logout\"", $content);
    
    file_put_contents($viewsAdminDir . $basename, $content);
}

// Create AdminController
$adminController = "<?php
class AdminController {
";

foreach ($adminFiles as $file) {
    $basename = basename($file, '.php');
    // convert dashboard_admin to dashboardAdmin
    $methodName = str_replace('_admin', '', $basename);
    if($methodName == 'pqrs') $methodName = 'lista';
    if(strpos($methodName, 'pqrs_') === 0) {
        $methodName = str_replace('pqrs_', '', $methodName);
    }
    
    $adminController .= "    public function {$methodName}() {
        require_once __DIR__ . '/../views/admin/{$basename}.php';
    }
";
}

$adminController .= "}
";

file_put_contents(__DIR__ . '/app/controllers/AdminController.php', $adminController);
echo "Admin migration complete.";
