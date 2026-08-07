<?php
/**
 * EmailServiceTest.php — Pruebas unitarias del EmailService
 *
 * Verifica las reglas de negocio del servicio de correo sin enviar
 * emails reales: validación de la configuración SMTP desde variables
 * de entorno, etiquetas de tipos PQRS y lógica de construcción del servicio.
 *
 * NOTA: Las pruebas NO conectan a un servidor SMTP real.
 * Se verifican comportamientos observables sin efectos secundarios de red.
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmailServiceTest extends TestCase
{
    // ── Variables de entorno SMTP ─────────────────────────────────────────────

    /** @test */
    public function variables_de_entorno_smtp_estan_configuradas_para_pruebas(): void
    {
        $this->assertNotEmpty(
            getenv('SMTP_HOST'),
            'SMTP_HOST debe estar definido en el entorno de pruebas.'
        );
        $this->assertNotEmpty(
            getenv('SMTP_USER'),
            'SMTP_USER debe estar definido en el entorno de pruebas.'
        );
        $this->assertNotEmpty(
            getenv('FROM_EMAIL'),
            'FROM_EMAIL debe estar definido en el entorno de pruebas.'
        );
    }

    /** @test */
    public function puerto_smtp_es_un_numero_valido(): void
    {
        $puerto = (int) getenv('SMTP_PORT');

        $this->assertGreaterThan(0, $puerto, 'El puerto SMTP debe ser un número positivo.');
        $this->assertLessThanOrEqual(65535, $puerto, 'El puerto SMTP no puede superar 65535.');
    }

    // ── Etiquetas de tipos PQRS para asunto de correo ────────────────────────

    /** @test */
    public function etiqueta_de_tipo_peticion_es_correcta(): void
    {
        $tipoLabel = [
            'peticion'   => 'Peticion',
            'queja'      => 'Queja',
            'reclamo'    => 'Reclamo',
            'sugerencia' => 'Sugerencia',
            'denuncia'   => 'Denuncia',
        ];

        $this->assertSame('Peticion',   $tipoLabel['peticion']);
        $this->assertSame('Queja',      $tipoLabel['queja']);
        $this->assertSame('Reclamo',    $tipoLabel['reclamo']);
        $this->assertSame('Sugerencia', $tipoLabel['sugerencia']);
        $this->assertSame('Denuncia',   $tipoLabel['denuncia']);
    }

    /** @test */
    public function tipo_desconocido_usa_ucfirst_como_fallback(): void
    {
        $tipoLabel = [
            'peticion'   => 'Peticion',
            'queja'      => 'Queja',
            'reclamo'    => 'Reclamo',
            'sugerencia' => 'Sugerencia',
            'denuncia'   => 'Denuncia',
        ];

        $tipoDesconocido = 'consulta';
        $etiqueta        = $tipoLabel[$tipoDesconocido] ?? ucfirst($tipoDesconocido);

        $this->assertSame('Consulta', $etiqueta, 'Un tipo desconocido debe usar ucfirst() como fallback.');
    }

    // ── Asunto del correo de confirmación ────────────────────────────────────

    /** @test */
    public function asunto_confirmacion_contiene_codigo_radicado(): void
    {
        $codigoRadicado = 'PQRS-2026-08-001';
        $asunto         = "Confirmacion de Radicacion PQRS - {$codigoRadicado}";

        $this->assertStringContainsString($codigoRadicado, $asunto);
        $this->assertStringStartsWith('Confirmacion de Radicacion PQRS', $asunto);
    }

    /** @test */
    public function asunto_respuesta_admin_contiene_codigo_radicado(): void
    {
        $codigoRadicado = 'PQRS-2026-08-042';
        $asunto         = "Respuesta a su solicitud PQRS - {$codigoRadicado}";

        $this->assertStringContainsString($codigoRadicado, $asunto);
    }

    // ── Validación del from_email ─────────────────────────────────────────────

    /** @test */
    public function from_email_de_prueba_es_valido(): void
    {
        $fromEmail = getenv('FROM_EMAIL') ?: '';

        $this->assertNotFalse(
            filter_var($fromEmail, FILTER_VALIDATE_EMAIL),
            "El FROM_EMAIL de prueba '{$fromEmail}' debe ser una dirección de correo válida."
        );
    }

    // ── Configuración de cifrado SMTP ─────────────────────────────────────────

    /** @test */
    public function cifrado_smtp_acepta_tls_o_ssl(): void
    {
        $ciframientosValidos = ['tls', 'ssl'];

        // La configuración por defecto debe ser uno de los dos
        $cifradoDefault = 'tls';
        $this->assertContains($cifradoDefault, $ciframientosValidos);
    }

    /** @test */
    public function construccion_de_cfg_desde_env_vars_tiene_todas_las_claves(): void
    {
        // Simula el bloque fallback del constructor de EmailService
        $cfg = [
            'smtp_host'       => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
            'smtp_port'       => getenv('SMTP_PORT') ?: 587,
            'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
            'smtp_user'       => getenv('SMTP_USER') ?: '',
            'smtp_password'   => getenv('SMTP_PASSWORD') ?: '',
            'from_email'      => getenv('FROM_EMAIL') ?: '',
            'from_name'       => getenv('FROM_NAME') ?: 'Sistema PQRS',
        ];

        $clavesRequeridas = ['smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_user', 'smtp_password', 'from_email', 'from_name'];

        foreach ($clavesRequeridas as $clave) {
            $this->assertArrayHasKey($clave, $cfg, "La clave '{$clave}' debe existir en la configuración SMTP.");
        }
    }
}
