# Sistema de Gestión de PQRS (Peticiones, Quejas, Reclamos, Sugerencias y Denuncias)

Bienvenido al **Sistema de Gestión de PQRS**. Es un sistema de atención ciudadana y corporativa diseñado para la radicación, seguimiento y resolución de solicitudes ciudadanas bajo los lineamientos de la normativa legal de Colombia (Ley 1755 de 2015 y Ley 1437 de 2011).

El sistema cuenta con dos portales principales:
1. **Portal del Ciudadano:** Permite radicar PQRS de forma pública o anónima, adjuntar archivos y consultar el estado y la respuesta formal en tiempo real usando un código único de radicado.
2. **Panel de Administración (Backoffice):** Permite a los gestores/administradores ver métricas en tiempo real, procesar solicitudes, emitir respuestas, generar reportes de cumplimiento y gestionar cuentas.

---

## 📚 Documentación y Manuales

| Documento | Descripción |
|-----------|-------------|
| 📋 [Especificación de Requisitos](docs/Especificacion_Requisitos.md) | Problemática, RF, RNF, flujo del sistema, Historias de Usuario por Sprint (Scrum), estados de PQRS y beneficios esperados |
| 📖 [Documentación Técnica](docs/documentacion-tecnica.md) | Arquitectura MVC, base de datos, módulos, seguridad, flujos internos, requisitos funcionales implementados e instalación |
| 👤 [Manual de Usuario](docs/Manual_de_Usuario.md) | Guía paso a paso para ciudadanos y administradores: radicar PQRS, consultar estado, gestionar solicitudes, reportes y configuración |
| 🔐 [Arquitectura y Seguridad](docs/ARQUITECTURA_Y_SEGURIDAD.md) | Patrones de Diseño (MVC, SOLID), Inyección de Dependencias, Prevención XSS/SQL Injection y enrutador estricto |

---

## 🚀 Características Principales

### 👥 Portal Ciudadano
* **Radicación Versátil:** Soporte para radicación de solicitudes como persona **Natural**, **Jurídica** o de forma **Anónima**.
* **Tipos de Solicitud Admitidos:** Peticiones, Quejas, Reclamos, Sugerencias y Denuncias.
* **Carga de Soportes:** Permite adjuntar archivos en formatos permitidos para soportar la solicitud.
* **Radicado Único:** Generación automática de código serial de seguimiento con formato `PQRS-AAAA-MM-NNN` (consecutivo mensual).
* **Consulta de Estado en Tiempo Real:** Interfaz intuitiva para que los ciudadanos conozcan el estado exacto de su solicitud (`PENDIENTE`, `EN_PROCESO`, `RESUELTO`, `RECHAZADO`) y visualicen la respuesta final.

### 🛡️ Módulo Administrativo
* **Dashboard Estadístico:** Panel con indicadores clave de rendimiento (KPIs), solicitudes pendientes, resueltas, tiempo promedio de respuesta y porcentaje de cumplimiento legal.
* **Gestión y Respuesta:** Flujo de trabajo completo para cambiar de estado, registrar el historial de acciones y responder formalmente a cada caso.
* **Gestión de Accesos y Seguridad:** Panel de login protegido y funciones de **recuperación y restablecimiento de contraseña** para administradores.
* **Generación de Reportes:** Creación de reportes estadísticos y de cumplimiento para periodos específicos. Exportación de datos en formato **Excel** y **PDF**.

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8.2 (Patrón MVC Estricto, Principios SOLID, Inyección de Dependencias, Namespaces PSR-4)
* **Base de Datos:** MySQL / MariaDB (Acceso seguro vía PDO y Sentencias Preparadas)
* **Diseño y Estilos:** Vanilla CSS moderno (responsive, variables CSS)
* **Iconografía:** [Bootstrap Icons](https://icons.getbootstrap.com/)
* **Gráficos:** [Chart.js](https://www.chartjs.org/)
* **Integración de Correos:** PHPMailer / SMTP (Confirmación de radicación y recuperación de contraseñas)
* **Generación de PDF:** DomPDF (`dompdf/dompdf`)

---

## 📂 Estructura del Proyecto

```text
PROYECTO_PQRS/
│
├── app/                     # Carpeta principal de la aplicación (MVC)
│   ├── controllers/         # Controladores Públicos (HomeController, PqrsController)
│   │   └── admin/           # Controladores Privados (AuthController, DashboardController, etc.)
│   ├── models/              # Modelos de Base de Datos (PDO, Consultas preparadas)
│   ├── services/            # Servicios externos (EmailService)
│   ├── core/                # Core de la app (Contenedor de Inyección de Dependencias)
│   └── views/               # Vistas separadas por módulos
│       ├── admin/           # Vistas del panel administrativo
│       ├── home/            # Vista de inicio principal
│       ├── layouts/         # Plantillas reutilizables (header, footer, modales)
│       └── pqrs/            # Vistas del portal ciudadano
│
├── config/                  # Archivos de configuración del sistema
│   └── email_config.php     # Ajustes de correo electrónico (PHPMailer SMTP)
│
├── public/                  # Recursos públicos del frontend
│   └── css/                 # Hojas de estilo
│       └── estilos.css      # Diseño unificado y responsive
│
├── vendor/                  # Dependencias de Composer (PHPMailer, DomPDF)
├── uploads/                 # Directorio de almacenamiento de archivos adjuntos
├── docs/                    # Documentación del proyecto
│   ├── documentacion-tecnica.md
│   ├── manual-usuario.md
│   ├── Especificacion_Requisitos.md
│   └── ARQUITECTURA_Y_SEGURIDAD.md
├── BD.txt                   # Script SQL de la Base de Datos
├── composer.json            # Dependencias PHP (PSR-4 Autoloader)
├── index.php                # Archivo principal de enrutamiento y DI Container (Front Controller)
└── README.md                # Presentación y enlaces a la documentación
```

---

## 🗄️ Configuración de la Base de Datos

El script de inicialización se encuentra en `BD.txt`. Contiene la creación de las siguientes tablas:

| Tabla | Descripción |
|-------|-------------|
| `administrador` | Cuentas administrativas con roles y último acceso |
| `usuario` | Información de remitentes (Natural, Jurídica o Anónima) |
| `configuracion_sistema` | Días de vencimiento por tipo de PQRS y datos de la empresa |
| `pqrs` | Registro principal de solicitudes (códigos, estados, fechas, respuestas) |
| `historial_accion` | Bitácora de auditoría de todos los cambios realizados por los gestores |
| `reporte` | Histórico de reportes generados desde la vista web, PDF y Excel |

### 📋 Pasos para la carga manual:
1. Crea una base de datos en tu servidor MySQL local (ej. `sistema_pqrs`).
2. Importa el archivo `BD.txt` a través de tu gestor de base de datos preferido (phpMyAdmin, MySQL Workbench, etc.) o ejecuta la consulta directa:
   ```bash
   mysql -u tu_usuario -p sistema_pqrs < BD.txt
   ```

---

## 💻 Instalación y Configuración Local (XAMPP / Laragon / WampServer)

Actualmente, el sistema está diseñado para ejecutarse de forma local. Sigue estos pasos para su configuración:

1. Clona el repositorio en el directorio web root de tu servidor local:
   ```bash
   git clone https://github.com/Santiago072/SistemaPQRS.git PROYECTO_PQRS
   ```
   * **XAMPP:** `C:\xampp\htdocs\PROYECTO_PQRS`
   * **Laragon:** `C:\laragon\www\PROYECTO_PQRS`

2. **Crea el archivo de configuración de correo** copiando el ejemplo incluido:
   ```bash
   cp config/email_config_example.php config/email_config.php
   ```
   Luego edita el archivo con tus credenciales reales. Este archivo está en `.gitignore` y **nunca se subirá al repositorio**.

3. La base de datos se configura internamente en `app/models/Database.php`. Por defecto asume `localhost`, usuario `root` y base de datos `sistema_pqrs`.

4. Edita `config/email_config.php` con tus credenciales SMTP (opcional, solo si quieres recibir correos):
   ```php
   'smtp_host'     => 'smtp.gmail.com',
   'smtp_port'     => 587,
   'smtp_user'     => 'tu_correo@gmail.com',
   'smtp_password' => 'xxxx xxxx xxxx xxxx',  // Contraseña de aplicación Gmail
   'from_email'    => 'tu_correo@gmail.com',
   'from_name'     => 'Sistema PQRS',
   ```
   > Para obtener una contraseña de aplicación en Gmail: Cuenta Google → Seguridad → Verificación en dos pasos → Contraseñas de aplicaciones.

5. Instala las dependencias con [Composer](https://getcomposer.org/):
   ```bash
   composer install
   ```

6. Crea la base de datos e importa el esquema:
   ```bash
   mysql -u root -p -e "CREATE DATABASE sistema_pqrs;"
   mysql -u root -p sistema_pqrs < BD.txt
   ```

7. Abre `http://localhost/PROYECTO_PQRS/` en tu navegador.

> **⚠️ Seguridad:** El archivo `config/email_config.php` está en `.gitignore`. Nunca lo subas al repositorio. Usa siempre el archivo `*_example.php` como plantilla.
