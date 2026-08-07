# 🏗️ Arquitectura y Seguridad del Sistema PQRS (v1.4.0)

Este documento valida formalmente las decisiones de arquitectura de software, patrones de diseño, flujos de persistencia, controles de seguridad e integración continua implementados en el **Sistema de Gestión de PQRS** bajo la normativa de Colombia (Ley 1755 de 2015 y Ley 1437 de 2011).

---

## 1. Diagrama de Componentes y Responsabilidades

```mermaid
graph TB
    subgraph CIUDADANO["👥 Portal Ciudadano (Frontend Público)"]
        direction TB
        UI_HOME["🏠 Vista Principal (Hero, Tipos, CTA)\napp/views/home/"]
        UI_FORM["📝 Formulario de Radicación\n(Natural / Jurídica / Anónima)\napp/views/pqrs/formulario.php"]
        UI_CONSULTA["🔍 Consulta en Tiempo Real\n(Línea de tiempo de hitos)\napp/views/pqrs/consulta.php"]
    end

    subgraph ADMIN["🛡️ Backoffice Administrativo (Frontend Privado)"]
        direction TB
        UI_LOGIN["🔐 Login & Recuperación\napp/views/admin/login.php"]
        UI_DASH["📊 Dashboard de KPIs\napp/views/admin/dashboard_admin.php"]
        UI_BANDEJA["📥 Bandeja de PQRS & Respuestas\napp/views/admin/pqrs*.php"]
        UI_CONFIG["⚙️ Configuración & Perfil\napp/views/admin/configuracion.php"]
        UI_REPORTES["📑 Reportes (Excel / PDF)\napp/views/admin/reportes.php"]
    end

    subgraph CORE["⚙️ Núcleo de Aplicación (MVC + IoC Container)"]
        direction TB
        ROUTER["🔀 Front Controller\nindex.php (?ruta=...)"]
        IOC["🧩 Inversión de Control (IoC)\napp/core/Container.php (Reflection API)"]

        subgraph CONTROLLERS["🎮 Controladores (SRP)"]
            CTRL_PQRS["PqrsController\nRadicación & Consulta"]
            CTRL_AUTH["AuthController\nBcrypt & Tokens 1h"]
            CTRL_DASH["DashboardController\nKPIs & Resumen"]
            CTRL_CONFIG["ConfigController\nParámetros & Perfil"]
            CTRL_REP["ReportController\nExportación Excel/PDF"]
        end
    end

    subgraph SERVICES["🔌 Capa de Servicios"]
        SRV_EMAIL["✉️ EmailService (PHPMailer)\nNotificaciones SMTP & Recuperación"]
        SRV_PDF["📄 DomPDF Engine\nGeneración de Reportes Landscape"]
    end

    subgraph DATA["🗄️ Capa de Persistencia & Base de Datos"]
        DB_SINGLETON["🔒 Database.php (PDO Singleton)\nSentencias Preparadas & Transacciones"]
        MODEL_PQRS["PqrsModel\n(Radicados, Vencimientos, Estados)"]
        MODEL_ADMIN["AdminModel\n(Usuarios, Contraseñas, Tokens)"]
        MODEL_CONFIG["ConfiguracionModel\n(Días de Término, Datos Institución)"]
        MODEL_USER["UsuarioModel\n(Remitentes & Datos de Contacto)"]

        MARIADB[("🛢️ MariaDB 10.11 / MySQL\nsistema_pqrs")]
    end

    %% Relaciones
    UI_HOME --> ROUTER
    UI_FORM --> ROUTER
    UI_CONSULTA --> ROUTER
    UI_LOGIN --> ROUTER
    UI_DASH --> ROUTER
    UI_BANDEJA --> ROUTER
    UI_CONFIG --> ROUTER
    UI_REPORTES --> ROUTER

    ROUTER --> IOC
    IOC --> CONTROLLERS

    CTRL_PQRS --> MODEL_PQRS
    CTRL_PQRS --> MODEL_USER
    CTRL_PQRS --> SRV_EMAIL

    CTRL_AUTH --> MODEL_ADMIN
    CTRL_AUTH --> SRV_EMAIL

    CTRL_DASH --> MODEL_PQRS
    CTRL_CONFIG --> MODEL_CONFIG
    CTRL_CONFIG --> MODEL_ADMIN

    CTRL_REP --> MODEL_PQRS
    CTRL_REP --> SRV_PDF

    MODEL_PQRS --> DB_SINGLETON
    MODEL_ADMIN --> DB_SINGLETON
    MODEL_CONFIG --> DB_SINGLETON
    MODEL_USER --> DB_SINGLETON

    DB_SINGLETON --> MARIADB
```

---

## 2. Matriz de Responsabilidades por Componente

| Capa | Componente | Principio SOLID | Responsabilidad Principal |
|------|------------|:---------------:|---------------------------|
| **Core** | `index.php` | OCP | Front Controller centralizado; resolución estricta de rutas |
| **Core** | `Container.php` | DIP | Inyección de dependencias recursiva mediante Reflection API de PHP |
| **Controlador** | `AuthController.php` | SRP | Autenticación con Bcrypt, sesiones blindadas y recuperación por tokens de 64 chars |
| **Controlador** | `PqrsController.php` | SRP | Radicación ciudadana, sanitización de archivos y consulta en tiempo real |
| **Controlador** | `DashboardController.php` | SRP | Cálculo de indicadores de rendimiento (KPIs), cumplimiento y estados |
| **Controlador** | `ConfigController.php` | SRP | Parametrización de días de vencimiento (1–30 días) y actualización de perfil admin |
| **Controlador** | `ReportController.php` | SRP | Generación y exportación de bitácoras en Excel y PDF horizontal |
| **Modelo** | `PqrsModel.php` | SRP | Consecutivo mensual `PQRS-AAAA-MM-NNN`, ciclo de vida y cálculo de vencimientos |
| **Modelo** | `AdminModel.php` | SRP | Consulta y actualización de credenciales, tokens temporales y último acceso |
| **Modelo** | `Database.php` | SRP | Conexión PDO Singleton con emulación desactivada y UTF-8mb4 estricto |
| **Servicio** | `EmailService.php` | DIP | Envío de correos SMTP (radicación, respuesta institucional y recuperación) |

---

## 3. Diagrama de Flujo: Radicación y Ciclo de Vida de PQRS

```mermaid
sequenceDiagram
    autonumber
    actor Ciudadano
    participant Router as index.php
    participant Ctrl as PqrsController
    participant Model as PqrsModel
    participant DB as MariaDB (PDO)
    participant SMTP as EmailService (PHPMailer)

    Ciudadano->>Router: POST /index.php?ruta=pqrs/guardar
    Router->>Ctrl: guardar() con inyección de PqrsModel
    Ctrl->>Ctrl: Sanitización de entradas (mb_substr, htmlspecialchars)
    Ctrl->>Ctrl: Validación de adjunto (PDF, JPG, PNG)
    Ctrl->>Model: generarCodigoRadicado()
    Model->>DB: SELECT MAX(consecutivo) WHERE Año=Actual AND Mes=Actual
    DB-->>Model: Último número
    Model-->>Ctrl: Código: PQRS-2026-08-001 (Zero-Padded)
    Ctrl->>Model: calcularFechaVencimiento(dias)
    Ctrl->>Model: crear(datos)
    Model->>DB: INSERT INTO pqrs (Prepared Statement)
    DB-->>Model: ID insertado
    
    opt Desea Notificación y tiene correo
        Ctrl->>SMTP: enviarConfirmacionRadicacion(correo, radicado, fecha)
        SMTP-->>Ciudadano: Correo con Radicado y Enlace de Consulta
    end

    Ctrl-->>Ciudadano: Vista de Confirmación con Botón Copiar Código
```

---

## 4. Diagrama de Seguridad: Recuperación de Contraseña con Validación en BD

```mermaid
sequenceDiagram
    autonumber
    actor Admin
    participant Ctrl as AuthController
    participant Model as AdminModel
    participant DB as MariaDB (administrador)
    participant SMTP as EmailService

    Admin->>Ctrl: POST ?ruta=admin/recuperar (correo)
    Ctrl->>Ctrl: filter_var(correo, FILTER_VALIDATE_EMAIL)
    Ctrl->>Model: obtenerPorCorreo(correo)
    Model->>DB: SELECT * FROM administrador WHERE correo_electronico=:correo AND estado='activo'
    
    alt Correo NO existe en la base de datos
        DB-->>Model: null
        Model-->>Ctrl: null
        Ctrl-->>Admin: ❌ Error: "El correo electrónico ingresado no se encuentra registrado en el sistema."
    else Correo existe y cuenta está activa
        DB-->>Model: Datos del administrador
        Model-->>Ctrl: [id, nombre, usuario, correo]
        Ctrl->>Ctrl: Generar token bin2hex(random_bytes(32)) -> 64 chars
        Ctrl->>Ctrl: expiracion = NOW() + 1 hora
        Ctrl->>Model: actualizarTokenRecuperacion(id, token, expiracion)
        Model->>DB: UPDATE administrador SET token_recuperacion, token_expiracion
        Ctrl->>SMTP: enviarCorreoRecuperacion(correo, urlReset con token)
        SMTP-->>Admin: Correo seguro con enlace de 1 solo uso (expira en 3600s)
        Ctrl-->>Admin: ✅ Éxito: "Se ha enviado un enlace de recuperación a su correo."
    end
```

---

## 5. Diagrama de DevOps e Integración Continua (CI/CD)

```mermaid
graph LR
    subgraph DEV["💻 Desarrollo Local"]
        CODE["Código PHP 8.2\n(MVC + IoC)"]
        TEST_LOCAL["composer test\n(37 tests / 86 assertions)"]
        GIT_PUSH["git push origin master"]

        CODE --> TEST_LOCAL --> GIT_PUSH
    end

    subgraph GITHUB["☁️ GitHub Actions CI/CD (.github/workflows/ci.yml)"]
        TRIGGER["Push / Pull Request"]
        SETUP["🐘 Setup PHP 8.2\n(mbstring, pdo, openssl)"]
        CACHE["🗄️ Cache Composer\n(composer.lock hash)"]
        INSTALL["📦 composer install\n--prefer-dist"]
        PHPUNIT["🧪 vendor/bin/phpunit\n--testdox (100% pasando)"]

        TRIGGER --> SETUP --> CACHE --> INSTALL --> PHPUNIT
    end

    subgraph PROD["🖥️ VPS Producción (Docker)"]
        DEPLOY["bash deploy.sh\n(fetch + reset + build)"]
        CADDY["Caddy / Nginx\nProxy Inverso + SSL"]
        CONTAINERS["Docker Compose\n(sistemapqrs_web + db)"]

        PHPUNIT -.->|Badge Verde ✅| DEPLOY
        DEPLOY --> CONTAINERS --> CADDY
    end
```

---

## 6. Suite de Pruebas Automatizadas (PHPUnit 10.5)

El sistema cuenta con **37 pruebas unitarias** y **86 aserciones** ejecutadas en menos de 0.7 segundos:

| Suite | Archivo | Pruebas | Alcance y Cobertura |
|-------|---------|:-------:|---------------------|
| **ContainerTest** | `tests/Unit/ContainerTest.php` | 6 | Inyección recursiva por Reflection API, singletons, control de clases abstractas y tipos primitivos |
| **AuthControllerTest** | `tests/Unit/AuthControllerTest.php` | 11 | Algoritmo Bcrypt, validación estricta de correo en tabla `administrador`, rechazo de correos no registrados, tokens de 64 chars y expiración en 3600s |
| **PqrsModelTest** | `tests/Unit/PqrsModelTest.php` | 11 | Formato `PQRS-AAAA-MM-NNN`, zero-padding, validación de 4 estados, 5 tipos de trámite y cálculo de vencimiento en días hábiles |
| **EmailServiceTest** | `tests/Unit/EmailServiceTest.php` | 9 | Configuración segura desde variables de entorno, validación de puertos SMTP, etiquetas en español y construcción de plantillas |

Para ejecutar localmente:
```bash
composer test          # Suite resumida
composer test-verbose  # Suite detallada con TestDox
```

---

## 7. Capa de Presentación Modular (CSS)

La hoja de estilos principal `public/css/estilos.css` actúa como orquestador limpio vía `@import` integrando 7 submódulos bajo `public/css/modules/`:

```text
public/css/
├── estilos.css           # Orquestador principal (@import)
└── modules/
    ├── variables.css     # Tokens de diseño, colores HSL/Hex, espaciado y sombras
    ├── base.css          # Reset universal, tipografía base y contenedores
    ├── layout.css        # Header sticky, footer corporativo, hero y CTA
    ├── components.css    # Botones interactivos, badges, cards, timeline y modal
    ├── forms.css         # Formularios de radicación, consulta y login
    ├── admin.css         # Dashboard de KPIs, métricas, filtros y reportes
    └── responsive.css    # Breakpoints 640px/768px/1024px y prefers-reduced-motion
```
