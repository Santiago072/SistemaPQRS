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
| 📜 [Registro de Cambios (Changelog)](CHANGELOG.md) | Historial detallado de todas las nuevas funcionalidades, versiones, correcciones de errores y actualizaciones técnicas |

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
SistemaPQRS/
│
├── app/                        # Carpeta principal de la aplicación (MVC)
│   ├── core/                   # Componentes base
│   │   └── Container.php       # Inyección de Dependencias automática
│   ├── controllers/            # Controladores Públicos (Portal Ciudadano)
│   │   ├── HomeController.php  # Página de inicio
│   │   ├── PqrsController.php  # Radicación y consulta pública de PQRS
│   │   └── admin/              # Controladores Privados (Backoffice)
│   │       ├── AuthController.php      # Login y recuperación de clave
│   │       ├── DashboardController.php # Panel de estadísticas
│   │       ├── ConfigController.php    # Configuración de días y perfil
│   │       ├── PqrsController.php      # Bandeja, respuesta y cambio de estado
│   │       └── ReportController.php    # Generación de métricas y Excel/PDF
│   ├── models/                 # Modelos de Base de Datos (PDO, Consultas)
│   │   ├── Database.php        # Conexión Singleton segura
│   │   ├── PqrsModel.php       # Consultas relacionadas con PQRS
│   │   ├── AdminModel.php      # Acceso a datos de administradores
│   │   ├── ConfiguracionModel.php # Parámetros globales del sistema
│   │   └── UsuarioModel.php    # Datos de ciudadanos/remitentes
│   ├── services/               # Servicios externos
│   │   └── EmailService.php    # Envío centralizado vía PHPMailer
│   └── views/                  # Vistas separadas por módulos (HTML puro)
│       ├── admin/              # Plantillas del panel administrativo
│       ├── home/               # Vista de inicio principal
│       ├── layouts/            # Plantillas maestras (header, footer, modales)
│       └── pqrs/               # Pantallas del portal ciudadano
│
├── config/                     # Archivos de configuración del sistema
│   ├── EnvLoader.php           # Lector nativo del archivo .env para XAMPP
│   └── email_config.php        # Ajustes manuales de SMTP (ignorado por Git)
│
├── public/                     # Recursos públicos del frontend
│   └── css/                    # Hojas de estilo unificadas
│       └── estilos.css         # Diseño responsivo y variables CSS
│
├── vendor/                     # Dependencias de Composer (PHPMailer, DomPDF)
├── uploads/                    # Directorio protegido de archivos adjuntos
├── docs/                       # Documentación técnica y manuales
├── BD.txt                      # Script SQL de la Base de Datos inicial
├── composer.json               # Lista de dependencias (PSR-4 Autoloader)
├── deploy.sh                   # Script de actualización automática en VPS
├── index.php                   # Front Controller (Recibe todas las peticiones)
├── .env.example                # Plantilla base de configuración
└── README.md                   # Presentación oficial del proyecto
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

### 📋 Pasos para la carga manual (Desarrollo Local):
1. Crea una base de datos en tu servidor MySQL local (ej. `sistema_pqrs`).
2. Importa el archivo `BD.txt` a través de tu gestor de base de datos preferido (phpMyAdmin, MySQL Workbench, etc.) o ejecuta la consulta directa:
   ```bash
   mysql -u tu_usuario -p sistema_pqrs < BD.txt
   ```

### 🐳 Restaurar Base de Datos en Docker (Producción):
Si ya tienes los contenedores corriendo y deseas importar una base de datos local hacia el VPS:
```bash
source .env
docker exec -i db mariadb -u $DB_USER -p$DB_PASSWORD $DB_NAME < BD.txt
```

---

## 🐳 Instalación y Configuración en Producción (Docker)

El sistema incluye una arquitectura orquestada y lista para entornos VPS o Producción mediante **Docker Compose**.

1. Clona el repositorio en tu servidor:
   ```bash
   git clone https://github.com/Santiago072/SistemaPQRS.git
   cd SistemaPQRS
   ```

2. **Variables de Entorno:**
   Copia el archivo de ejemplo para configurar tus credenciales seguras. Este archivo (`.env`) no se subirá a GitHub.
   ```bash
   cp .env.example .env
   nano .env
   ```
   Asegúrate de incluir `APP_BASE=/` para que el sistema detecte la raíz `/` en los contenedores. Configura tus puertos, base de datos y credenciales SMTP.

3. Construye e inicia los servicios utilizando el script de automatización avanzado:
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```
   *(Este script asegura permisos correctos locales, limpia la caché de git mediante `fetch` + `reset --hard`, y finalmente ejecuta `docker compose up -d --build` garantizando un despliegue sin conflictos).*

4. Para exponer el proyecto hacia Internet con un dominio, te recomendamos conectarlo a un proxy inverso como **Nginx nativo** o Nginx Proxy Manager, apuntando al puerto expuesto (ej. `8892`).

---

## 💻 Instalación Local (XAMPP / Laragon / WampServer)

Si prefieres trabajar en desarrollo de forma tradicional:

1. Clona el repositorio en el directorio web root:
   * **XAMPP:** `C:\xampp\htdocs\SistemaPQRS`
   * **Laragon:** `C:\laragon\www\SistemaPQRS`

2. Configuración de Correo Electrónico (Opcional pero recomendado):
   ```bash
   cp config/email_config_example.php config/email_config.php
   ```
   Edita tus credenciales SMTP en ese archivo.

3. Importa el archivo `BD.txt` a tu servidor MySQL en una nueva base de datos llamada `sistema_pqrs`.

4. Abre `http://localhost/SistemaPQRS/` en tu navegador.

---

## 📜 Versionamiento y Cambios

Este proyecto se adhiere al [Versionamiento Semántico](https://semver.org/lang/es/) y mantiene un registro detallado de todas sus actualizaciones de cara al cliente y al servidor.

Para ver el historial detallado de las nuevas funcionalidades, correcciones de errores y actualizaciones técnicas del sistema, por favor consulta nuestro archivo [CHANGELOG.md](CHANGELOG.md).
