<?php
/**
 * ContainerTest.php — Pruebas unitarias del IoC Container
 *
 * Verifica que el Container (Inversión de Control) resuelva correctamente
 * las dependencias usando Reflection API, devuelva singletons y
 * lance excepciones descriptivas ante clases no instanciables.
 */

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Container;
use PHPUnit\Framework\TestCase;

// ─── Clases auxiliares para las pruebas (sin base de datos) ──────────────────

class ClaseSinConstructor
{
    public function saludar(): string
    {
        return 'hola';
    }
}

class ClaseConDependencia
{
    public ClaseSinConstructor $dep;

    public function __construct(ClaseSinConstructor $dep)
    {
        $this->dep = $dep;
    }
}

class ClaseConPrimitivo
{
    public function __construct(string $nombre) {}
}

abstract class ClaseAbstracta {}

// ─── Tests ───────────────────────────────────────────────────────────────────

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    /** @test */
    public function resuelve_clase_sin_constructor(): void
    {
        $instancia = $this->container->get(ClaseSinConstructor::class);

        $this->assertInstanceOf(ClaseSinConstructor::class, $instancia);
        $this->assertSame('hola', $instancia->saludar());
    }

    /** @test */
    public function resuelve_clase_con_dependencia_inyectada(): void
    {
        $instancia = $this->container->get(ClaseConDependencia::class);

        $this->assertInstanceOf(ClaseConDependencia::class, $instancia);
        $this->assertInstanceOf(ClaseSinConstructor::class, $instancia->dep);
    }

    /** @test */
    public function devuelve_mismo_singleton_en_llamadas_sucesivas(): void
    {
        $primera  = $this->container->get(ClaseSinConstructor::class);
        $segunda  = $this->container->get(ClaseSinConstructor::class);

        $this->assertSame($primera, $segunda, 'El Container debe devolver la misma instancia (singleton).');
    }

    /** @test */
    public function lanza_excepcion_si_clase_no_existe(): void
    {
        $this->expectException(\Exception::class);

        $this->container->get('App\\ClaseQueNoExiste');
    }

    /** @test */
    public function lanza_excepcion_si_parametro_es_tipo_primitivo(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/nombre|primitivo|resolver/i');

        $this->container->get(ClaseConPrimitivo::class);
    }

    /** @test */
    public function lanza_excepcion_si_clase_es_abstracta(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/instanciable/i');

        $this->container->get(ClaseAbstracta::class);
    }
}
