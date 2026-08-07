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

### Protección contra Spam (Rate Limiting)
- Se implementó un limitador de peticiones basado en sesiones de PHP para la creación de PQRS.
- Impone un tiempo de espera ("cooldown") de 120 segundos entre cada solicitud enviada por un mismo usuario, mitigando ataques de denegación de servicio (DoS) a nivel de aplicación y previniendo la saturación de la base de datos por *bots* o *scripts* automatizados.

### Validación y Truncamiento de Entradas (Buffer Overflow)
- **Frontend**: Todos los campos de texto HTML cuentan con atributos `maxlength` estrictos, alineados con el esquema de la base de datos (Ej. 150 caracteres para nombres).
- **Backend**: Los controladores utilizan la función `mb_substr()` nativa de PHP para truncar de manera forzosa y segura el texto (respetando caracteres multibyte UTF-8) a la longitud exacta que soporta el motor de base de datos antes de enviarlo. Esto protege al sistema contra peticiones forjadas que buscan generar errores de desbordamiento por *payloads* masivos enviados fuera del navegador.

## 4. Requisitos y Dependencias (Composer)
- **PHP 8.2 o superior**
- **PHPMailer** (para notificaciones por correo vía SMTP)
- **DomPDF** (para la generación de reportes en formato PDF)
- **PHPUnit 10.5** (suite de pruebas unitarias automatizadas en entorno de desarrollo)

El proyecto utiliza **PSR-4** a través de `composer.json` para la carga automática de clases. Para registrar nuevos componentes basta con ejecutar:
```bash
composer dump-autoload
```

---

## 5. Suite de Pruebas Automatizadas (PHPUnit 10.5)

El sistema incluye una suite de **35 pruebas unitarias** con **80 aserciones** que validan la lógica de negocio y arquitectura sin requerir conexión a base de datos externa:

| Test Suite | Ubicación | Pruebas | Qué valida |
|------------|-----------|:-------:|------------|
| **ContainerTest** | `tests/Unit/ContainerTest.php` | 6 | Resolución de dependencias vía Reflection API, instancias Singleton, excepciones ante clases abstractas y tipos primitivos |
| **AuthControllerTest** | `tests/Unit/AuthControllerTest.php` | 9 | Hashing y verificación con Bcrypt, unicidad y formato de tokens de recuperación (64 chars hex), expiración en 1 hora y validación de emails |
| **PqrsModelTest** | `tests/Unit/PqrsModelTest.php` | 11 | Formato de código radicado serial `PQRS-AAAA-MM-NNN`, relleno con ceros (zero-padding), 4 estados de ciclo de vida, 5 tipos de solicitud y cálculo de vencimiento |
| **EmailServiceTest** | `tests/Unit/EmailServiceTest.php` | 9 | Configuración segura desde variables de entorno, validación de puertos SMTP, etiquetas de tipo en español y estructura de mensajes |

Para ejecutar las pruebas localmente:
```bash
composer test          # Modo estándar
composer test-verbose  # Modo detallado con TestDox
```

---

## 6. Integración Continua (CI/CD con GitHub Actions)

El archivo `.github/workflows/ci.yml` automatiza la verificación de calidad del código en la nube:
- **Disparadores**: Cada `push` o `pull_request` sobre las ramas `master` o `main`.
- **Entorno**: Contenedor Ubuntu con **PHP 8.2** y extensiones (`mbstring`, `pdo`, `pdo_mysql`, `openssl`, `fileinfo`).
- **Pipeline**:
  1. `actions/checkout@v4` — Descarga del código fuente.
  2. `shivammathur/setup-php@v2` — Configuración del runtime PHP 8.2.
  3. `actions/cache@v4` — Almacenamiento en caché de dependencias `vendor/` basado en el hash de `composer.lock`.
  4. `composer install --prefer-dist` — Instalación reproducible de dependencias.
  5. `vendor/bin/phpunit --testdox` — Ejecución de los 35 tests automatizados.

---

## 7. Arquitectura Modular del Frontend (CSS)

La hoja de estilos `public/css/estilos.css` está organizada bajo una arquitectura modular en `public/css/modules/` para facilitar el mantenimiento y eliminar la complejidad de archivos monolíticos:

- `variables.css` — Custom properties, paleta de colores HSL/Hex, tipografía, espaciados y sombras.
- `base.css` — Reset universal, estilos de elementos base, contenedores y utilidades globales.
- `layout.css` — Header sticky, footer corporativo, sección Hero del portal ciudadano y banners CTA.
- `components.css` — Botones interactivos, badges de estado, cuadrícula de cards, línea de tiempo, tabla legal y modales.
- `forms.css` — Formulario de radicación con selector de persona, consulta pública de PQRS y login administrativo.
- `admin.css` — Dashboard de KPIs, métricas en tiempo real, filtros avanzados de búsqueda, alertas de urgencia y reportes.
- `responsive.css` — Breakpoints adaptativos (640px, 768px, 1024px), adaptaciones móviles y soporte para `prefers-reduced-motion`.

