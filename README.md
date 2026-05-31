# Sistema de Gestión de PQRS (Peticiones, Quejas, Reclamos, Sugerencias y Denuncias)

Bienvenido al **Sistema de Gestión de PQRS**. Es un sistema de atención ciudadana y corporativa diseñado para la radicación, seguimiento y resolución de solicitudes ciudadanas bajo los lineamientos de la normativa legal de Colombia (Ley 1755 de 2015 y Ley 1437 de 2011).

El sistema cuenta con dos portales principales:
1. **Portal del Ciudadano:** Permite radicar PQRS de forma pública o anónima, adjuntar archivos y consultar el estado y la respuesta formal en tiempo real usando un código único de radicado.
2. **Panel de Administración (Backoffice):** Permite a los gestores/administradores ver métricas en tiempo real, procesar solicitudes, emitir respuestas, generar reportes de cumplimiento y gestionar cuentas.

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

* **Backend:** PHP 8.2 (Estructurado y modularizado)
* **Base de Datos:** MySQL / MariaDB
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
├── administrador/           # Panel de control administrativo
│   ├── dashboard_admin.php  # Tablero estadístico principal con KPIs
│   ├── login.php            # Autenticación de administradores
│   ├── logout.php           # Cierre de sesión
│   ├── recuperar_contrasena.php # Interfaz para solicitar recuperación de clave
│   ├── restablecer_contrasena.php # Funcionalidad para actualizar la nueva clave
│   ├── pqrs.php             # Bandeja con listado y filtros de todas las PQRS
│   ├── pqrs_cambiar_estado.php # Cambio de estado e historial
│   ├── pqrs_historial.php   # Timeline de acciones por PQRS
│   ├── pqrs_responder.php   # Interfaz para formular respuestas a ciudadanos
│   ├── pqrs_ver.php         # Vista de detalle y auditoría de la PQRS
│   ├── reportes.php         # Generador de reportes
│   ├── exportar_excel.php   # Exportación Excel
│   └── exportar_pdf.php     # Exportación PDF (DomPDF)
│
├── config/                  # Archivos de configuración del sistema
│   ├── conexion.php         # Conexión MySQLi a base de datos
│   └── email_config.php     # Ajustes de correo electrónico (PHPMailer SMTP)
│
├── css/                     # Hojas de estilo
│   └── estilos.css          # Diseño unificado y responsive
│
├── includes/                # Fragmentos reutilizables y funciones de utilidad
│   ├── header.php           # Encabezado responsive común
│   ├── footer.php           # Pie de página informativo con enlaces
│   ├── funciones.php        # Utilidades transversales
│   ├── verificar_sesion.php # Middleware de seguridad para el área administrativa
│   └── modal_terminos.php   # Modal de Términos y Condiciones
│
├── pqrs/                    # Módulos accesibles por el ciudadano
│   ├── tipos.php            # Selección visual del tipo de PQRS
│   ├── formulario.php       # Formulario dinámico de radicación
│   ├── consulta_pqrs.php    # Buscador y rastreador de solicitudes
│   └── confirmacion.php     # Pantalla de confirmación con radicado y resumen
│
├── vendor/                  # Dependencias de Composer (PHPMailer, DomPDF)
├── uploads/                 # Directorio de almacenamiento de archivos adjuntos
├── BD.txt                   # Script SQL de la Base de Datos
├── composer.json            # Dependencias PHP (Composer)
├── index.php                # Página de inicio del portal del ciudadano
└── README.md                # Documentación del proyecto
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

1. Clona o copia el repositorio en el directorio web root de tu servidor local:
   * **XAMPP:** `C:\xampp\htdocs\PROYECTO_PQRS`
   * **Laragon:** `C:\laragon\www\PROYECTO_PQRS`

2. Revisa la conexión a la base de datos en `config/conexion.php`. Asegúrate de que las credenciales (`$host`, `$user`, `$pass`, `$db`) coincidan con tu entorno local:
   ```php
   function conexion() {
       $host = 'localhost';
       $user = 'root';
       $pass = ''; // Por defecto en XAMPP es vacío
       $db   = 'sistema_pqrs';
       
       $conexion = mysqli_connect($host, $user, $pass, $db);
       if (!$conexion) die("Error de conexión: " . mysqli_connect_error());
       return $conexion;
   }
   ```

3. Configura el envío de correos (opcional). El sistema usa PHPMailer mediante SMTP. Si deseas habilitar las notificaciones por correo electrónico y el restablecimiento de contraseñas, asegúrate de colocar credenciales válidas en `config/email_config.php` y habilitar la extensión `openssl` en tu `php.ini`.

4. Instala las dependencias con [Composer](https://getcomposer.org/) abriendo la terminal en la carpeta del proyecto:
   ```bash
   composer install
   ```

5. Abre `http://localhost/PROYECTO_PQRS/` en tu navegador para empezar a usar el sistema.

> **Nota de Seguridad:** Se recomienda no subir al repositorio archivos de configuración (`email_config.php` o `conexion.php`) que contengan contraseñas reales o datos confidenciales.
