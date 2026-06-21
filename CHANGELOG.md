# Registro de Cambios (Changelog)

Todos los cambios notables de este proyecto se documentarán en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere al [Versionamiento Semántico](https://semver.org/lang/es/).

## [v1.1.0] - 2026-06-21
### Agregado
- **Soporte Docker para Producción**: Implementación de orquestación con `docker-compose.yml` utilizando PHP 8.2 FPM, Caddy (servidor web interno) y MariaDB 10.11.
- **Variables de Entorno (.env)**: Soporte nativo para variables de entorno para una configuración segura de la base de datos y SMTP en producción, sin exponer credenciales en el código fuente.
- **Integración con Redes Externas**: El `docker-compose.yml` permite unir el contenedor directamente a redes de proxy inverso (ej. Nginx Proxy Manager o Sodicol Network).
- Sistema de versionamiento con este archivo `CHANGELOG.md`.

### Modificado
- Refactorización masiva de nombres de rutas: se renombró el antiguo esquema `PROYECTO_PQRS` a `SistemaPQRS` de forma global para unificar el entorno de trabajo y el repositorio.
- El archivo `app/models/Database.php` ahora lee prioritariamente las credenciales desde las variables de entorno (`DB_HOST`, `DB_NAME`, etc.) con un *fallback* a los valores de desarrollo local.
- `index.php` ajustado para leer el entorno dinámicamente y detectar si está bajo un entorno aislado (como Docker) a través de la variable `RAILWAY_ENVIRONMENT`, permitiendo setear la ruta base en `/`.

### Corregido
- Corrección de la configuración de red y volumen para la inicialización automática de la base de datos con `BD.sql` en entornos Docker.
