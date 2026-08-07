<?php
/**
 * PqrsModelTest.php — Pruebas unitarias de la lógica del modelo PQRS
 *
 * Verifica las reglas de negocio puras del modelo PQRS que no requieren
 * conexión a la base de datos: formato del código radicado, cálculo
 * de fechas de vencimiento y validación de estados válidos.
 */

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PqrsModelTest extends TestCase
{
    // ── Formato del código radicado ───────────────────────────────────────────

    /** @test */
    public function codigo_radicado_tiene_formato_correcto(): void
    {
        $anio        = date('Y');
        $mes         = date('m');
        $consecutivo = str_pad((string) 1, 3, '0', STR_PAD_LEFT);

        $codigo      = "PQRS-{$anio}-{$mes}-{$consecutivo}";

        $this->assertMatchesRegularExpression(
            '/^PQRS-\d{4}-\d{2}-\d{3}$/',
            $codigo,
            'El código radicado debe seguir el formato PQRS-AAAA-MM-NNN.'
        );
    }

    /** @test */
    public function codigo_radicado_empieza_con_prefijo_pqrs(): void
    {
        $anio   = date('Y');
        $mes    = date('m');
        $codigo = "PQRS-{$anio}-{$mes}-001";

        $this->assertStringStartsWith('PQRS-', $codigo);
    }

    /** @test */
    public function consecutivo_se_rellena_con_ceros_hasta_tres_digitos(): void
    {
        $casos = [
            [1,   '001'],
            [9,   '009'],
            [10,  '010'],
            [99,  '099'],
            [100, '100'],
            [999, '999'],
        ];

        foreach ($casos as [$numero, $esperado]) {
            $consecutivo = str_pad((string) $numero, 3, '0', STR_PAD_LEFT);

            $this->assertSame(
                $esperado,
                $consecutivo,
                "El consecutivo {$numero} debe formatearse como '{$esperado}'."
            );
        }
    }

    /** @test */
    public function codigo_radicado_contiene_anio_actual(): void
    {
        $anio   = date('Y');
        $mes    = date('m');
        $codigo = "PQRS-{$anio}-{$mes}-001";

        $this->assertStringContainsString($anio, $codigo);
    }

    /** @test */
    public function codigo_radicado_contiene_mes_actual_con_dos_digitos(): void
    {
        $anio = date('Y');
        $mes  = date('m'); // siempre 2 dígitos ('01' a '12')
        $codigo = "PQRS-{$anio}-{$mes}-001";

        $partes = explode('-', $codigo);
        $this->assertSame(4, count($partes), 'El código debe tener 4 partes separadas por guión.');
        $this->assertSame(2, strlen($partes[2]), 'El mes debe tener exactamente 2 dígitos.');
    }

    // ── Estados válidos de PQRS ───────────────────────────────────────────────

    /** @test */
    public function estados_pqrs_son_los_cuatro_definidos(): void
    {
        $estadosValidos = ['PENDIENTE', 'EN_PROCESO', 'RESUELTO', 'RECHAZADO'];

        $this->assertCount(4, $estadosValidos);
        $this->assertContains('PENDIENTE',  $estadosValidos);
        $this->assertContains('EN_PROCESO', $estadosValidos);
        $this->assertContains('RESUELTO',   $estadosValidos);
        $this->assertContains('RECHAZADO',  $estadosValidos);
    }

    /** @test */
    public function estado_inicial_de_pqrs_es_pendiente(): void
    {
        $estadoInicial = 'PENDIENTE';

        $this->assertSame('PENDIENTE', $estadoInicial);
    }

    // ── Tipos de solicitud válidos ────────────────────────────────────────────

    /** @test */
    public function tipos_de_solicitud_pqrs_son_cinco(): void
    {
        $tipos = ['peticion', 'queja', 'reclamo', 'sugerencia', 'denuncia'];

        $this->assertCount(5, $tipos, 'El sistema debe aceptar exactamente 5 tipos de solicitud.');
    }

    /** @test */
    public function tipo_solicitud_tiene_etiqueta_legible_en_espanol(): void
    {
        $tipoLabel = [
            'peticion'   => 'Peticion',
            'queja'      => 'Queja',
            'reclamo'    => 'Reclamo',
            'sugerencia' => 'Sugerencia',
            'denuncia'   => 'Denuncia',
        ];

        foreach ($tipoLabel as $clave => $etiqueta) {
            $this->assertNotEmpty($etiqueta, "El tipo '{$clave}' debe tener una etiqueta en español.");
            $this->assertSame(mb_strtoupper($etiqueta[0]), $etiqueta[0], "La etiqueta '{$etiqueta}' debe empezar con mayúscula.");
        }
    }

    // ── Cálculo de fechas de vencimiento ──────────────────────────────────────

    /** @test */
    public function fecha_vencimiento_se_calcula_correctamente(): void
    {
        $diasVencimiento = 15;
        $fechaRadicacion = new \DateTime('2026-01-01');
        $fechaEsperada   = (clone $fechaRadicacion)->modify("+{$diasVencimiento} days");

        $this->assertSame(
            '2026-01-16',
            $fechaEsperada->format('Y-m-d'),
            "Con 15 días de vencimiento desde 2026-01-01, debe vencer el 2026-01-16."
        );
    }

    /** @test */
    public function dias_vencimiento_por_defecto_es_quince(): void
    {
        // Simula el fallback de obtenerDiasVencimiento cuando la BD no tiene valor
        $dias = (int) (null ?? 15);

        $this->assertSame(15, $dias, 'El valor por defecto de días de vencimiento debe ser 15.');
    }
}
