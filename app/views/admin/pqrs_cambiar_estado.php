<?php
/**
 * pqrs_cambiar_estado.php — Vista/Puente de cambio de estado
 *
 * Toda la lógica de negocio fue movida a AdminController::pqrs_cambiar_estado().
 * Este archivo ahora solo recibe el POST y lo reenvía al controlador vía ruta MVC.
 *
 * Si se llega aquí directamente (sin pasar por el enrutador), redirigir al panel.
 */
header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '/SistemaPQRS/') . 'index.php?ruta=admin/pqrs');
exit;
