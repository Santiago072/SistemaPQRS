# Arquitectura y Seguridad del Sistema PQRS

Este documento describe detalladamente la arquitectura de software, los patrones de diseño implementados y las medidas de seguridad del Sistema de Peticiones, Quejas, Reclamos, Sugerencias y Denuncias (PQRS).

## 1. Arquitectura de Software

El sistema está construido bajo el patrón **Modelo-Vista-Controlador (MVC)**, garantizando una separación clara entre la lógica de negocio, la interacción con la base de datos y la interfaz de usuario.

### Estructura de Directorios (PSR-4)
- **`app/controllers/`**: Contiene los controladores que procesan las peticiones HTTP (GET/POST).
- **`app/models/`**: Contiene las clases que interactúan con la base de datos mediante sentencias preparadas.
- **`app/views/`**: Contiene los archivos HTML/PHP de presentación.
- **`app/services/`**: Contiene servicios externos, como `EmailService` para el envío de correos (PHPMailer).
- **`app/core/`**: Contiene componentes base, como el Contenedor de Inyección de Dependencias.
- **`config/`**: Archivos de configuración del sistema (ej. `email_config.php`).
- **`.env`**: Archivo de variables de entorno para credenciales de base de datos y SMTP, cumpliendo con la metodología de *12-Factor App* y el principio OCP.

### Front Controller y Enrutamiento Estricto
Todas las solicitudes web pasan por un único punto de entrada: `index.php`. Este archivo actúa como **Front Controller** e implementa un mapa estricto de rutas.
- Las URLs mantienen el formato `?ruta=modulo/accion`, asegurando compatibilidad hacia atrás.
- Las rutas no definidas explícitamente lanzan un error `404 Not Found`.

## 2. Implementación de Principios SOLID

El sistema ha sido refactorizado para cumplir con estándares empresariales mediante los principios SOLID:

1. **[S] Single Responsibility Principle (SRP)**:
   - El monolítico `AdminController` fue fragmentado en controladores especializados ubicados en `app/controllers/admin/`:
     - `AuthController`: Maneja exclusivamente inicio de sesión, cierre de sesión y recuperación de contraseñas.
     - `DashboardController`: Carga las estadísticas iniciales.
     - `ConfigController`: Gestiona los ajustes del perfil de administrador y las variables del sistema.
     - `PqrsController`: Administra el flujo de los tickets (ver, cambiar estado, responder).
     - `ReportController`: Especializado en generar exportaciones (PDF y Excel).

2. **[O] Open/Closed Principle (OCP)**:
   - El enrutador basado en mapas (Arrays) permite agregar nuevas rutas y controladores sin tener que modificar la lógica central de inicialización.

3. **[D] Dependency Inversion Principle (DIP)**:
   - **Inyección de Dependencias**: Los controladores ya no instancian los modelos usando `new ClassName()` internamente.
   - En su lugar, el `Container` de `app/core/Container.php` utiliza la **API de Reflexión** de PHP para analizar los parámetros del constructor e inyectar automáticamente los objetos necesarios.
   - *Ejemplo*: `public function __construct(PqrsModel $pqrsModel)`. Esto desacopla el código y facilita futuras pruebas unitarias (Unit Testing con Mocks).

## 3. Seguridad Implementada

### Prevención de Inyección SQL (CWE-89)
- **Cero funciones obsoletas**: Se erradicó por completo el uso de funciones `mysqli_*`.
- **PDO (PHP Data Objects)**: Todas las consultas a la base de datos se ejecutan obligatoriamente mediante sentencias preparadas y parámetros enlazados (`$stmt->execute(['param' => $value])`). Esto garantiza inmunidad total contra SQL Injection.

### Prevención de XSS (Cross-Site Scripting) (CWE-79)
- En todas las vistas (`app/views/`), las variables provenientes de la base de datos o de peticiones de usuarios se renderizan utilizando la función `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.
- Esto neutraliza la inyección de etiquetas HTML maliciosas o scripts de Javascript.

### Seguridad en Sesiones e Inicio de Sesión
- **Protección de Contraseñas**: Las contraseñas nunca se guardan en texto plano. Se emplea `password_hash()` con el algoritmo robusto BCRYPT. Su validación se hace con `password_verify()`.
- **Protección contra CSRF y Secuestro de Sesión**: Las sesiones están protegidas. El acceso a rutas de administración exige la presencia del ID de sesión; si no existe, redirige inmediatamente al login.
- **Recuperación Segura**: Los tokens de recuperación de contraseñas se generan criptográficamente mediante `random_bytes()`, con una caducidad preestablecida en la base de datos de 1 hora.

### Carga de Archivos
La subida de evidencias soporta extensiones limitadas (PDF, JPG, PNG) y se reescriben los nombres para evitar la sobreescritura accidental o inyección de archivos ejecutables en el servidor web.

## 4. Requisitos y Dependencias (Composer)
- **PHP 8.0 o superior**
- **PHPMailer** (para notificaciones por correo vía SMTP)
- **DomPDF** (para la generación de reportes en formato PDF)

El proyecto utiliza **PSR-4** a través de `composer.json` para la carga automática de clases. Para registrar nuevos componentes basta con ejecutar:
```bash
composer dump-autoload
```
