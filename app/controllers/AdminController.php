<?php
class AdminController {
    public function actualizar_perfil() {
        require_once __DIR__ . '/../views/admin/actualizar_perfil.php';
    }
    public function alertas() {
        require_once __DIR__ . '/../views/admin/alertas.php';
    }
    public function configuracion() {
        require_once __DIR__ . '/../views/admin/configuracion.php';
    }
    public function dashboard() {
        require_once __DIR__ . '/../views/admin/dashboard_admin.php';
    }
    public function exportar_excel() {
        require_once __DIR__ . '/../views/admin/exportar_excel.php';
    }
    public function exportar_pdf() {
        require_once __DIR__ . '/../views/admin/exportar_pdf.php';
    }
    public function login() {
        require_once __DIR__ . '/../views/admin/login.php';
    }
    public function logout() {
        require_once __DIR__ . '/../views/admin/logout.php';
    }
    public function pqrs() {
        require_once __DIR__ . '/../views/admin/pqrs.php';
    }
    public function pqrs_cambiar_estado() {
        require_once __DIR__ . '/../views/admin/pqrs_cambiar_estado.php';
    }
    public function pqrs_historial() {
        require_once __DIR__ . '/../views/admin/pqrs_historial.php';
    }
    public function pqrs_responder() {
        require_once __DIR__ . '/../views/admin/pqrs_responder.php';
    }
    public function pqrs_ver() {
        require_once __DIR__ . '/../views/admin/pqrs_ver.php';
    }
    public function recuperar() {
        require_once __DIR__ . '/../views/admin/recuperar_contrasena.php';
    }
    public function recuperar_contrasena() {
        require_once __DIR__ . '/../views/admin/recuperar_contrasena.php';
    }
    public function reportes() {
        require_once __DIR__ . '/../views/admin/reportes.php';
    }
    public function restablecer_contrasena() {
        require_once __DIR__ . '/../views/admin/restablecer_contrasena.php';
    }
}
