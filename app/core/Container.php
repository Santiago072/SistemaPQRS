<?php
namespace App\Core;

use Exception;
use ReflectionClass;
use ReflectionException;

class Container
{
    /**
     * @var array Instancias únicas para singletons
     */
    private array $instances = [];

    /**
     * Resuelve y crea una instancia de la clase solicitada, inyectando dependencias.
     *
     * @param string $class Nombre completo de la clase (namespace + clase)
     * @return mixed
     * @throws Exception Si la clase no puede resolverse
     */
    public function get(string $class)
    {
        // Si ya tenemos una instancia (Singleton en contexto de solicitud), la devolvemos
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }

        try {
            $reflection = new ReflectionClass($class);

            // Verificar si es instanciable
            if (!$reflection->isInstantiable()) {
                throw new Exception("La clase {$class} no es instanciable.");
            }

            $constructor = $reflection->getConstructor();

            // Si no tiene constructor, instanciar directamente
            if (is_null($constructor)) {
                $instance = new $class();
                $this->instances[$class] = $instance;
                return $instance;
            }

            // Resolver parámetros del constructor
            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();

                if (!$type || $type->isBuiltin()) {
                    throw new Exception("No se puede resolver el parámetro {$parameter->getName()} de {$class} porque no tiene un tipo de clase definido o es un tipo primitivo.");
                }

                // Llamada recursiva para resolver la dependencia
                $dependencyClass = $type->getName();
                $dependencies[] = $this->get($dependencyClass);
            }

            // Instanciar con dependencias inyectadas
            $instance = $reflection->newInstanceArgs($dependencies);
            $this->instances[$class] = $instance;
            
            return $instance;

        } catch (ReflectionException $e) {
            throw new Exception("Error resolviendo la clase {$class}: " . $e->getMessage());
        }
    }
}
