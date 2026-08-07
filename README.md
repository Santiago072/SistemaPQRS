# 📑 Sistema de Gestión de PQRS — Atención Ciudadana (v1.4.0)

Bienvenido al **Sistema de Gestión de PQRS**. Es un sistema web integral de atención ciudadana y corporativa diseñado para la radicación, seguimiento y resolución de solicitudes ciudadanas bajo los lineamientos de la normativa legal de Colombia (**Ley 1755 de 2015** y **Ley 1437 de 2011**), desarrollado en PHP nativo 8.2 con arquitectura MVC, Inversión de Control (IoC Container) y pruebas automatizadas con PHPUnit.

El sistema cuenta con dos portales principales:
1. **👥 Portal del Ciudadano:** Permite radicar PQRS de forma pública (Persona Natural o Jurídica) o anónima, adjuntar archivos de soporte y consultar el estado y la respuesta formal en tiempo real usando un código único de radicado serial (`PQRS-AAAA-MM-NNN`).
2. **🛡️ Panel de Administración (Backoffice):** Permite a los gestores ver métricas e indicadores de cumplimiento en tiempo real, procesar solicitudes, emitir respuestas con plantillas dinámicas, generar reportes en Excel/PDF y gestionar la configuración institucional.

---

| Documento | Descripción |
|-----------|-------------|
| 👤 [Manual de Usuario](docs/Manual_de_Usuario.md) | Guía de uso de la aplicación para usuarios finales |
| 📜 [Registro de Cambios](CHANGELOG.md) | Historial de versiones y modificaciones del sistema (v1.4.0) |
| 📋 [Plan de Implementación](docs/PLAN_DE_IMPLEMENTACION.md) | Fases del proyecto, stack tecnológico y arquitectura de atención ciudadana |
| 📖 [Documentación Técnica](docs/documentacion-tecnica.md) | Arquitectura MVC, base de datos, módulos, seguridad, CI/CD y flujos internos |
| 📋 [Especificación de Requisitos](docs/Especificacion_Requisitos.md) | Problemática, RF, RNF, flujo del sistema, Historias de Usuario por Sprint (Scrum) |
| 🚀 [Manual de Despliegue VPS](docs/DESPLIEGUE_VPS.md) | Guía paso a paso para instalar y actualizar en el VPS con Docker y Nginx |
| 🏗️ [Arquitectura y Componentes](docs/ARQUITECTURA_Y_SEGURIDAD.md) | Diagramas y patrones: MVC, SOLID, IoC Container, seguridad y Docker |
| 🤝 [Guía para Colaboradores](docs/CONTRIBUTING.md) | Configuración local, convenciones de commits y checklist de PR |
| ⚖️ [Licencia MIT](LICENSE) | Términos legales de propiedad intelectual y uso abierto |


---

## 🚀 Características Principales

### 👥 Portal Ciudadano
* **Radicación Versátil:** Soporte para radicación de solicitudes como persona **Natural**, **Jurídica** o de forma **Anónima**.
* **Tipos de Solicitud Admitidos:** Peticiones, Quejas, Reclamos, Sugerencias y Denuncias con cálculo automático de días hábiles de vencimiento.
* **Carga de Soportes:** Permite adjuntar archivos en formatos permitidos para soportar la solicitud con sanitización estricta.
* **Radicado Único:** Generación automática de código serial de seguimiento con formato `PQRS-AAAA-MM-NNN` (consecutivo mensual con relleno a 3 dígitos).
* **Consulta de Estado en Tiempo Real:** Interfaz intuitiva para que los ciudadanos conozcan el estado exacto de su solicitud (`PENDIENTE`, `EN_PROCESO`, `RESUELTO`, `RECHAZADO`), la línea de tiempo de hitos y la respuesta formal del administrador.

### 🛡️ Módulo Administrativo
* **Dashboard Estadístico:** Panel con indicadores clave de rendimiento (KPIs), solicitudes pendientes, en proceso, resueltas, rechazadas, vencidas y porcentaje de cumplimiento legal.
* **Gestión y Respuesta:** Flujo de trabajo completo para cambiar de estado, registrar el historial de acciones y responder formalmente con plantillas rápidas.
* **Gestión de Accesos y Seguridad:** Panel de login protegido con Bcrypt, sesiones PHP blindadas y funciones de **recuperación y restablecimiento de contraseña vía tokens criptográficos de un solo uso**.
* **Generación de Reportes:** Creación de reportes estadísticos y de cumplimiento para periodos específicos. Exportación de datos en formato **Excel** y **PDF** (orientación horizontal completa).

---

## 🛠️ Stack Tecnológico

* **Backend:** PHP 8.2 (Patrón MVC Estricto, Principios SOLID, IoC Container con Reflection API, Namespaces PSR-4).
* **Base de Datos:** MySQL / MariaDB 10.11 (Acceso seguro vía PDO Singleton, Sentencias Preparadas y Transacciones).
* **Pruebas Automatizadas:** [PHPUnit 10.5](https://phpunit.de/) (35 pruebas unitarias automatizadas: Container, AuthController, PqrsModel, EmailService).
* **Integración Continua (CI/CD):** [GitHub Actions](.github/workflows/ci.yml) (validación automática en PHP 8.2 en cada push/PR).
* **Frontend y Estilos:** Vanilla CSS modularizado en 7 módulos bajo `public/css/modules/` (variables, base, layout, components, forms, admin, responsive).
* **Iconografía:** [Bootstrap Icons](https://icons.getbootstrap.com/)
* **Gráficos:** [Chart.js](https://www.chartjs.org/)
* **Integración de Correos:** PHPMailer / SMTP (confirmación de radicación y recuperación de credenciales).
* **Generación de PDF:** DomPDF (`dompdf/dompdf`).

---

## 🧪 Pruebas Automatizadas (PHPUnit 10.5)

El sistema incluye una suite de **35 pruebas unitarias** con **80 aserciones** que validan el comportamiento del IoC Container, lógica de autenticación, generación de radicados y servicios SMTP:

```bash
# Ejecutar la suite completa
composer test

# Ejecutar con salida detallada (testdox)
composer test-verbose
```

---

## 📂 Estructura del Proyecto

```text
SistemaPQRS/
│
├── .github/workflows/         # Integración Continua (CI/CD)
│   └── ci.yml                 # Workflow de GitHub Actions (PHP 8.2 + PHPUnit)
│
├── app/                       # Carpeta principal de la aplicación (MVC)
│   ├── core/                  # Componentes base del framework
│   │   └── Container.php      # Inyección de Dependencias automática (Reflection API)
│   ├── controllers/           # Controladores Públicos (Portal Ciudadano)
│   │   ├── HomeController.php # Página de inicio
│   │   ├── PqrsController.php # Radicación y consulta pública de PQRS
│   │   └── Admin/             # Controladores Privados (Backoffice)
│   │       ├── AuthController.php      # Login y recuperación de clave (Bcrypt + Tokens)
│   │       ├── DashboardController.php # Panel de estadísticas y KPIs
│   │       ├── ConfigController.php    # Configuración de días de vencimiento y perfil
│   │       ├── PqrsController.php      # Bandeja, respuesta y cambio de estado
│   │       └── ReportController.php    # Generación de métricas y exportación Excel/PDF
│   ├── models/                # Modelos de Base de Datos (PDO Singleton)
│   │   ├── Database.php       # Conexión Singleton segura
│   │   ├── PqrsModel.php      # Consultas y reglas de negocio de PQRS
│   │   ├── AdminModel.php     # Acceso a datos de administradores
│   │   ├── ConfiguracionModel.php # Parámetros institucionales
│   │   └── UsuarioModel.php   # Datos de ciudadanos y remitentes
│   ├── services/              # Servicios de integración
│   │   └── EmailService.php   # Envío centralizado vía PHPMailer / SMTP
│   └── views/                 # Vistas separadas por módulos
│       ├── admin/             # Plantillas del panel administrativo
│       ├── home/              # Vista de inicio principal
│       ├── layouts/           # Plantillas maestras (header, footer, modales)
│       └── pqrs/              # Pantallas del portal ciudadano
│
├── config/                    # Archivos de configuración
│   ├── EnvLoader.php          # Lector nativo del archivo .env para XAMPP
│   └── email_config.php       # Ajustes manuales de SMTP (ignorado por Git)
│
├── public/                    # Recursos públicos del frontend
│   └── css/                   # Hojas de estilo modularizadas
│       ├── estilos.css        # Orquestador principal (@import)
│       └── modules/           # 7 módulos CSS especializados
│           ├── variables.css  # Tokens, colores, espaciado, sombras
│           ├── base.css       # Reset, tipografía y utilidades
│           ├── layout.css     # Header, footer, hero y CTA
│           ├── components.css # Botones, cards, timeline, modales
│           ├── forms.css      # Radicación, consulta y login
│           ├── admin.css      # Dashboard, métricas, bandeja y filtros
│           └── responsive.css # Breakpoints 640px, 768px, 1024px
│
├── tests/                     # Suite de pruebas automatizadas
│   ├── bootstrap.php          # Autoloader y entorno de prueba
│   └── Unit/                  # Pruebas unitarias
│       ├── ContainerTest.php     # 6 pruebas de IoC Container
│       ├── AuthControllerTest.php # 9 pruebas de autenticación y tokens
│       ├── PqrsModelTest.php     # 11 pruebas de radicados y reglas
│       └── EmailServiceTest.php  # 9 pruebas de configuración SMTP
│
├── docs/                      # Documentación técnica y manuales
├── uploads/                   # Directorio de archivos adjuntos (protegido)
├── BD.txt                     # Script SQL de la Base de Datos inicial
├── composer.json              # Dependencias y scripts de prueba (PSR-4)
├── deploy.sh                  # Script de despliegue automatizado en VPS
├── docker-compose.yml         # Orquestación de contenedores en producción
├── Dockerfile                 # Imagen Docker optimizada (PHP 8.2 + Caddy)
├── phpunit.xml                # Configuración de PHPUnit 10.5
├── LICENSE                    # Licencia MIT de código abierto
├── index.php                  # Front Controller (Enrutador principal)
├── .env.example               # Plantilla base de variables de entorno
└── README.md                  # Presentación oficial del proyecto
```

---

## 🗄️ Configuración de la Base de Datos

El script de inicialización se encuentra en `BD.txt`. Contiene la creación de las siguientes tablas:

| Tabla | Descripción |
|-------|-------------|
| `administrador` | Cuentas administrativas con roles, estado y último acceso |
| `usuario` | Información de remitentes (Persona Natural, Jurídica o Anónima) |
| `configuracion_sistema` | Días de vencimiento por tipo de PQRS y datos de la institución |
| `pqrs` | Registro principal de solicitudes (códigos, estados, fechas, respuestas) |
| `historial_accion` | Bitácora de auditoría de todos los cambios realizados por los gestores |
| `reporte` | Histórico de reportes generados desde la vista web, PDF y Excel |

### 📋 Pasos para la carga manual (Desarrollo Local):
1. Crea una base de datos en tu servidor MySQL local (ej. `sistema_pqrs`).
2. Importa el archivo `BD.txt` a través de tu gestor preferido (phpMyAdmin, MySQL Workbench) o ejecuta:
   ```bash
   mysql -u tu_usuario -p sistema_pqrs < BD.txt
   ```

### 🐳 Restaurar Base de Datos en Docker (Producción):
Si los contenedores ya están en ejecución y deseas importar los datos:
```bash
source .env
docker exec -i db mariadb -u $DB_USER -p$DB_PASSWORD $DB_NAME < BD.txt
```

---

## 🐳 Instalación y Despliegue en Producción (Docker)

El sistema incluye orquestación lista para entornos VPS con **Docker Compose**:

1. Clona el repositorio en el servidor:
   ```bash
   git clone https://github.com/Santiago072/SistemaPQRS.git
   cd SistemaPQRS
   ```

2. **Variables de Entorno:**
   Copia la plantilla y configura tus credenciales seguras:
   ```bash
   cp .env.example .env
   nano .env
   ```
   Asegúrate de incluir `APP_BASE=/` para contenedores Docker.

3. Despliega con el script automatizado:
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```

---

## 💻 Instalación Local (XAMPP / Laragon)

1. Clona el repositorio en tu directorio web:
   * **XAMPP:** `C:\xampp\htdocs\SistemaPQRS`
   * **Laragon:** `C:\laragon\www\SistemaPQRS`

2. Instala las dependencias y ejecuta las pruebas:
   ```bash
   composer install
   composer test
   ```

3. Importa `BD.txt` en MySQL en una base de datos llamada `sistema_pqrs`.

4. Abre en tu navegador:
   ```
   http://localhost/SistemaPQRS/
   ```

---

## 📜 Versionamiento y Cambios

Este proyecto se adhiere al [Versionamiento Semántico (SemVer)](https://semver.org/lang/es/) y mantiene un registro detallado de todas sus actualizaciones en el archivo [CHANGELOG.md](CHANGELOG.md).
