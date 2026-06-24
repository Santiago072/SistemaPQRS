<?php
namespace App\Config;

/**
 * EnvLoader — Responsabilidad Única: cargar variables de entorno desde un archivo .env.
 */
class EnvLoader
{
    /**
     * Carga el archivo .env al array $_ENV.
     * Solo procesa líneas con el formato KEY=VALUE.
     * Ignora comentarios (#) y líneas vacías.
     * No sobreescribe variables ya definidas (respeta variables de entorno reales del SO).
     *
     * @param string $path Ruta absoluta al archivo .env
     */
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);

            // Ignorar comentarios y líneas sin '='
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // No sobreescribir variables de entorno reales del sistema operativo
            if (!array_key_exists($key, $_ENV) && getenv($key) === false) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}
