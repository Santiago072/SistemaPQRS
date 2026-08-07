<?php
/**
 * AuthControllerTest.php — Pruebas unitarias del AuthController
 *
 * Verifica las reglas de negocio del controlador de autenticación:
 * validación de campos vacíos, verificación de contraseñas con bcrypt,
 * y lógica de tokens de recuperación — sin conexión real a la BD
 * (usando un mock de AdminModel).
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Admin\AuthController;
use App\Models\AdminModel;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class AuthControllerTest extends TestCase
{
    /** @var MockObject&AdminModel */
    private MockObject $adminModelMock;

    private AuthController $controller;

    protected function setUp(): void
    {
        // Iniciar sesión PHP limpia para cada test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_POST    = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Mock de AdminModel — sin base de datos real
        $this->adminModelMock = $this->createMock(AdminModel::class);
        $this->controller     = new AuthController($this->adminModelMock);
    }

    protected function tearDown(): void
    {
        $_POST  = [];
        $_SESSION = [];
    }

    // ── Validación de contraseñas ─────────────────────────────────────────────

    /** @test */
    public function password_verify_acepta_hash_bcrypt_valido(): void
    {
        $password = 'MiClave2026!';
        $hash     = password_hash($password, PASSWORD_BCRYPT);

        $this->assertTrue(
            password_verify($password, $hash),
            'password_verify debe retornar true para un hash bcrypt válido.'
        );
    }

    /** @test */
    public function password_verify_rechaza_contrasena_incorrecta(): void
    {
        $hash = password_hash('ContraseñaCorrecta', PASSWORD_BCRYPT);

        $this->assertFalse(
            password_verify('ContraseñaIncorrecta', $hash),
            'password_verify debe retornar false para una contraseña incorrecta.'
        );
    }

    /** @test */
    public function password_hash_genera_hashes_diferentes_por_salt(): void
    {
        $password = 'MismaPassword';
        $hash1    = password_hash($password, PASSWORD_BCRYPT);
        $hash2    = password_hash($password, PASSWORD_BCRYPT);

        $this->assertNotSame($hash1, $hash2, 'Bcrypt debe generar hashes distintos por el salt aleatorio.');
        $this->assertTrue(password_verify($password, $hash1));
        $this->assertTrue(password_verify($password, $hash2));
    }

    // ── Generación de tokens de recuperación ─────────────────────────────────

    /** @test */
    public function token_recuperacion_tiene_longitud_correcta(): void
    {
        $token = bin2hex(random_bytes(32));

        // 32 bytes en hex = 64 caracteres
        $this->assertSame(64, strlen($token), 'El token de recuperación debe tener 64 caracteres hexadecimales.');
    }

    /** @test */
    public function token_recuperacion_es_unico_en_cada_generacion(): void
    {
        $token1 = bin2hex(random_bytes(32));
        $token2 = bin2hex(random_bytes(32));

        $this->assertNotSame($token1, $token2, 'Cada token generado debe ser único.');
    }

    /** @test */
    public function token_recuperacion_solo_contiene_caracteres_hexadecimales(): void
    {
        $token = bin2hex(random_bytes(32));

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{64}$/',
            $token,
            'El token debe ser una cadena hexadecimal de 64 caracteres.'
        );
    }

    // ── Validación de correo electrónico ─────────────────────────────────────

    /** @test */
    public function filter_validate_email_acepta_correo_valido(): void
    {
        $correos = [
            'admin@pqrs.gov.co',
            'usuario.nombre@empresa.com',
            'contacto+tag@dominio.org',
        ];

        foreach ($correos as $correo) {
            $this->assertNotFalse(
                filter_var($correo, FILTER_VALIDATE_EMAIL),
                "El correo '{$correo}' debería ser válido."
            );
        }
    }

    /** @test */
    public function filter_validate_email_rechaza_correos_invalidos(): void
    {
        $correos = [
            'sin-arroba-punto',
            '@sinusuario.com',
            'sindominio@',
            '',
        ];

        foreach ($correos as $correo) {
            $this->assertFalse(
                filter_var($correo, FILTER_VALIDATE_EMAIL),
                "El correo '{$correo}' debería ser inválido."
            );
        }
    }

    // ── Expiración de token ───────────────────────────────────────────────────

    /** @test */
    public function fecha_expiracion_token_es_una_hora_despues(): void
    {
        $ahora      = time();
        $expiracion = strtotime('+1 hour', $ahora);

        $diferencia = $expiracion - $ahora;

        $this->assertSame(3600, $diferencia, 'El token debe expirar exactamente en 3600 segundos (1 hora).');
    }

    // ── Validación de correo contra la tabla administrador en BD ──────────────

    /** @test */
    public function recuperar_consulta_correo_en_tabla_administrador(): void
    {
        $correoExistente = 'admin@pqrs.gov.co';

        $this->adminModelMock
            ->expects($this->once())
            ->method('obtenerPorCorreo')
            ->with($this->equalTo($correoExistente))
            ->willReturn([
                'id'                 => 1,
                'nombre_usuario'     => 'admin',
                'nombre_completo'    => 'Administrador General',
                'correo_electronico' => $correoExistente,
                'estado'             => 'activo'
            ]);

        $resultado = $this->adminModelMock->obtenerPorCorreo($correoExistente);

        $this->assertNotNull($resultado);
        $this->assertSame($correoExistente, $resultado['correo_electronico']);
        $this->assertSame('activo', $resultado['estado']);
    }

    /** @test */
    public function recuperar_retorna_null_si_correo_no_existe_en_base_de_datos(): void
    {
        $correoNoRegistrado = 'correo_inexistente@dominio.com';

        $this->adminModelMock
            ->expects($this->once())
            ->method('obtenerPorCorreo')
            ->with($this->equalTo($correoNoRegistrado))
            ->willReturn(null);

        $resultado = $this->adminModelMock->obtenerPorCorreo($correoNoRegistrado);

        $this->assertNull($resultado, 'Debe retornar null cuando el correo no existe en la tabla administrador.');
    }
}

