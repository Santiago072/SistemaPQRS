# Registro de Cambios (Changelog)

Todos los cambios notables de este proyecto se documentarán en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto se adhiere al [Versionamiento Semántico](https://semver.org/lang/es/).

## [v1.2.0] - 2026-06-23
### Agregado
- **Soporte Nativo Local (.env)**: Se agregó la clase `EnvLoader.php` para parsear automáticamente el archivo `.env` en entornos de desarrollo local como XAMPP, unificando la lógica con el entorno de producción.
- **Limitador de Tasa (Rate Limiting)**: Se integró una protección anti-spam por sesión (cooldown de 120 segundos) para evitar el envío masivo de PQRS.
- **Seguridad de Formularios**: Implementación forzosa de truncamiento por backend (`mb_substr`) alineado con los límites de la base de datos para prevenir *Buffer Overflow* y cargas maliciosas. Los inputs HTML ahora tienen `maxlength`.
- **Automatización de Despliegue Avanzado**: Se reescribió el script `deploy.sh` en la raíz del proyecto para sincronizar ramas forzosamente, reparar permisos de Docker, y prevenir conflictos de Git.
- **Enlaces Legales Estables**: Se actualizaron los enlaces del Marco Legal (footer) para apuntar directamente a los repositorios oficiales de la **Secretaría del Senado** (Ley 1755, Ley 1437, y se añadió la Ley 1581 de Protección de Datos).

### Modificado
- **Refactorización de Entorno (Agnosticismo)**: Se eliminó la dependencia *hardcoded* (`RAILWAY_ENVIRONMENT`) en favor de la variable estándar `APP_BASE`. El proyecto ahora soporta cualquier VPS de manera oficial y agnóstica.
- **Documentación de Arquitectura**: Se amplió drásticamente el `README.md` detallando la función de cada controlador, modelo y vista dentro del patrón MVC.

### Modificado
- **UX Administrador**: Ahora, al elegir una plantilla de respuesta (Ej. Resuelto, En Proceso), el sistema auto-selecciona el estado correspondiente en la lista desplegable de forma inteligente y lo resalta visualmente.
- **Experiencia de Usuario (Ciudadano)**: El botón "Nueva Solicitud" del footer ahora abre el modal de aceptación de términos globalmente sin importar en qué vista se encuentre el usuario.

### Corregido
- **Variables indefinidas**: Solucionado un *warning* estricto de PHP 8.2 en `pqrs_responder.php` cuando las variables de éxito/error no estaban definidas al cargar la vista por primera vez.

## [v1.1.1] - 2026-06-21
### Corregido
- **Autoloading (PSR-4)**: Se resolvió un error crítico de *Case Sensitivity* (sensibilidad a mayúsculas) al desplegar en servidores Linux. Se renombró el directorio `app/controllers/admin` a `app/controllers/Admin` para que coincida exactamente con el Namespace de Composer, eliminando el error de "Class does not exist" en el panel administrativo.

## [v1.1.0] - 2026-06-21
### Agregado
- **Soporte Docker para Producción**: Implementación de orquestación con `docker-compose.yml` utilizando PHP 8.2 FPM, Caddy (servidor web interno) y MariaDB 10.11.
- **Variables de Entorno (.env)**: Soporte nativo para variables de entorno para una configuración segura de la base de datos y SMTP en producción, sin exponer credenciales en el código fuente.
- **Integración con Redes Externas**: El `docker-compose.yml` permite unir el contenedor directamente a redes de proxy inverso (ej. Nginx Proxy Manager o Sodicol Network).
- Sistema de versionamiento con este archivo `CHANGELOG.md`.

### Modificado
- Refactorización masiva de nombres de rutas: se renombró el antiguo esquema `PROYECTO_PQRS` a `SistemaPQRS` de forma global para unificar el entorno de trabajo y el repositorio.
- El archivo `app/models/Database.php` ahora lee prioritariamente las credenciales desde las variables de entorno (`DB_HOST`, `DB_NAME`, etc.) con un *fallback* a los valores de desarrollo local.
- `index.php` ajustado para leer el entorno dinámicamente (`EnvLoader.php`) y detectar la variable `APP_BASE`, permitiendo enrutar correctamente tanto en XAMPP como en Docker/VPS.

### Corregido
- Corrección de la configuración de red y volumen para la inicialización automática de la base de datos con `BD.sql` en entornos Docker.
