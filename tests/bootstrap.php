<?php
/**
 * tests/bootstrap.php — Bootstrap de PHPUnit para SistemaPQRS
 *
 * Responsabilidad: configurar el entorno de pruebas antes de que se ejecute
 * cualquier test. Carga el autoloader de Composer y define las constantes
 * globales que el sistema requiere (BASE_PATH, APP_ENV, etc.).
 *
 * NO conecta a la base de datos real: las pruebas unitarias usan mocks o datos
 * en memoria. Solo el entorno de integración (futuro) usaría conexión real.
 */

declare(strict_types=1);

// ── 1. Autoloader de Composer ─────────────────────────────────────────────────
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';

if (!file_exists($autoloader)) {
    fwrite(STDERR, "ERROR: vendor/autoload.php no encontrado.\n");
    fwrite(STDERR, "Ejecuta: composer install\n");
    exit(1);
}

require_once $autoloader;

// ── 2. Constantes globales del sistema ───────────────────────────────────────
// El router de SistemaPQRS usa BASE_PATH para construir URLs.
// En pruebas se define como cadena vacía para evitar errores de redirección.
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '');
}

if (!defined('APP_ENV')) {
    define('APP_ENV', 'testing');
}

// ── 3. Variables de entorno de prueba (fallback si phpunit.xml no las inyectó) ─
$envDefaults = [
    'APP_ENV'        => 'testing',
    'DB_HOST'        => 'localhost',
    'DB_NAME'        => 'sistema_pqrs_test',
    'DB_USER'        => 'root',
    'DB_PASSWORD'    => '',
    'SMTP_HOST'      => 'smtp.test.local',
    'SMTP_PORT'      => '587',
    'SMTP_USER'      => 'test@test.local',
    'SMTP_PASSWORD'  => 'test_password',
    'FROM_EMAIL'     => 'noreply@test.local',
    'FROM_NAME'      => 'PQRS Test',
];

foreach ($envDefaults as $key => $value) {
    if (getenv($key) === false) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
}

// ── 4. Zona horaria para pruebas reproducibles ────────────────────────────────
date_default_timezone_set('America/Bogota');
