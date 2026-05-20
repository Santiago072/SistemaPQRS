# Sistema de Gestión de PQRS (Peticiones, Quejas, Reclamos, Sugerencias y Denuncias)

¡Bienvenido al **Sistema de Gestión de PQRS**! Este es un sistema de atención ciudadana y corporativa de nivel profesional, diseñado para la radicación, seguimiento y resolución de solicitudes ciudadanas bajo los lineamientos de la normativa legal de Colombia (Ley 1755 de 2015 y Ley 1437 de 2011).

El sistema cuenta con dos portales principales:
1. **Portal del Ciudadano:** Permite radicar PQRS de forma pública o anónima, adjuntar archivos y consultar el estado y la respuesta formal en tiempo real usando un código único de radicado.
2. **Panel de Administración (Backoffice):** Permite a los gestores/administradores ver métricas en tiempo real, procesar solicitudes, emitir respuestas, gestionar alertas de vencimiento, generar reportes de cumplimiento y exportar información en formato PDF y Excel.

---

## 🚀 Características Principales

### 👥 Portal Ciudadano
* **Radicación Versátil:** Soporte para radicación de solicitudes como persona **Natural**, **Jurídica** o de forma **Anónima**.
* **Tipos de Solicitud Admitidos:** Peticiones, Quejas, Reclamos, Sugerencias y Denuncias.
* **Carga de Soportes:** Permite adjuntar archivos en formatos permitidos para soportar la solicitud.
* **Radicado Único:** Generación automática de código serial de seguimiento con formato `PQRS-AAAA-MMMM-NNN`.
* **Consulta de Estado en Tiempo Real:** Interfaz intuitiva para que los ciudadanos conozcan el estado exacto de su solicitud (`PENDIENTE`, `EN_PROCESO`, `RESUELTO`, `RECHAZADO`) y visualicen la respuesta final.

### 🛡️ Módulo Administrativo
* **Dashboard Estadístico:** Panel con indicadores clave de rendimiento (KPIs), solicitudes pendientes, resueltas, tiempo promedio de respuesta y porcentaje de cumplimiento legal.
* **Gestión y Respuesta:** Flujo de trabajo completo para asignar, cambiar de estado (`EN_PROCESO`, `RESUELTO`, `RECHAZADO`), registrar el historial de acciones y responder formalmente a cada caso.
* **Sistema de Alertas y Semáforos:** Alertas visuales y lógicas de vencimiento basadas en los términos de la Ley 1755 de 2015.
* **Generación de Reportes:** Creación automática de reportes estadísticos y de cumplimiento para periodos específicos.
* **Exportación de Datos:** Descarga de reportes detallados en formato **Excel** y **PDF** estructurados y listos para presentación ejecutiva.
* **Configuración del Sistema:** Interfaz gráfica para ajustar dinámicamente los días hábiles límite por cada tipo de PQRS, el nombre de la empresa y los correos de notificaciones.

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 8.2 (Estructurado y modularizado)
* **Base de Datos:** MySQL / MariaDB
* **Diseño y Estilos:** Vanilla CSS moderno (Glassmorphism, responsive, variables CSS para modo oscuro y branding del sistema)
* **Iconografía:** [Bootstrap Icons](https://icons.getbootstrap.com/)
* **Integración de Correos:** PHPMailer y API de SendGrid
* **Generación de PDF:** DomPDF (`dompdf/dompdf`)
* **Contenedores y Despliegue:** Docker, Caddy Server como servidor web de producción y compatibilidad nativa con Railway.

---

## 📂 Estructura del Proyecto

A continuación se detalla la organización de los archivos principales del repositorio:

```text
PROYECTO_PQRS/
│
├── administrador/           # Panel de control administrativo
│   ├── alertas.php          # Control de alertas de vencimiento
│   ├── dashboard_admin.php  # Tablero estadístico principal con KPIs
│   ├── login.php / logout.php# Control de sesiones administrativas
│   ├── pqrs.php             # Listado y filtros de todas las PQRS
│   ├── pqrs_responder.php   # Interfaz para formular respuestas a ciudadanos
│   ├── pqrs_ver.php         # Vista de detalle y auditoría de la PQRS
│   ├── reportes.php         # Generador de reportes de cumplimiento
│   ├── exportar_excel.php   # Motor de exportación Excel
│   └── exportar_pdf.php     # Motor de exportación PDF (DomPDF)
│
├── config/                  # Archivos de configuración del sistema
│   ├── conexion.php         # Conexión PDO/MySQLi a base de datos (Railway o Local)
│   └── email_config.php     # Ajustes de correo electrónico (PHPMailer / SendGrid)
│
├── css/                     # Hojas de estilo y assets visuales
│   └── estilos.css          # Diseño unificado, moderno e inclusivo
│
├── includes/                # Fragmentos reutilizables y funciones de utilidad
│   ├── header.php           # Encabezado responsive común
│   ├── footer.php           # Pie de página informativo con enlaces
│   ├── funciones.php        # Utilidades transversales (radicación, fechas hábiles, etc.)
│   ├── verificar_sesion.php # Middleware de seguridad para el área administrativa
│   └── modal_terminos.php   # Modal de Términos y Condiciones (Política de Datos - Habeas Data)
│
├── pqrs/                    # Módulos accesibles por el ciudadano
│   ├── formulario.php       # Formulario interactivo de registro de PQRS
│   ├── consulta_pqrs.php    # Buscador y rastreador de solicitudes
│   └── confirmacion.php     # Pantalla de confirmación con radicado y resumen
│
├── vendor/                  # Dependencias de composer (PHPMailer, DomPDF, etc.)
├── BD.txt                   # Script SQL estructurado de la Base de Datos
├── Dockerfile               # Configuración del contenedor Docker con soporte Caddy
├── composer.json            # Declaración de dependencias de PHP (Composer)
├── index.php                # Página de inicio del portal del ciudadano
└── README.md                # Documentación del proyecto (Este archivo)
```

---

## 🗄️ Configuración de la Base de Datos

El script de inicialización de la base de datos se encuentra en [BD.txt](file:///c:/xampp/htdocs/PROYECTO_PQRS/BD.txt). Contiene la creación de las siguientes tablas clave:
* `administrador`: Cuentas administrativas con roles y último acceso.
* `usuario`: Información de remitentes (Persona Natural, Jurídica o Anónima).
* `configuracion_sistema`: Configuración de vencimientos y metadatos empresariales.
* `pqrs`: Registro principal de PQRS (códigos, estados, fechas, respuestas).
* `historial_accion`: Bitácora y traza de auditoría de los cambios de estado hechos por los gestores.
* `alerta_vencimiento`: Registro para alertas del sistema de semáforos.
* `reporte`: Consolidado histórico de reportes generados.

### 📋 Pasos para la carga manual:
1. Crea una base de datos en tu servidor MySQL (ej. `sistema_pqrs` o `railway`).
2. Importa el archivo SQL ubicado en `BD.txt` a través de tu gestor de base de datos preferido (phpMyAdmin, DBeaver, MySQL Workbench, etc.) o ejecuta la consulta directa:
   ```bash
   mysql -u tu_usuario -p tu_base_datos < BD.txt
   ```

---

## 💻 Instalación y Configuración Local

### Opción 1: Servidor Local Tradicional (XAMPP / Laragon / WampServer)
1. Clona o copia el contenido de este repositorio en el directorio web root:
   * **XAMPP:** `C:\xampp\htdocs\PROYECTO_PQRS`
   * **Laragon:** `C:\laragon\www\PROYECTO_PQRS`
2. Configura los parámetros de conexión en [config/conexion.php](file:///c:/xampp/htdocs/PROYECTO_PQRS/config/conexion.php). Modifica la función para usar tus credenciales de `localhost`:
   ```php
   function conexion() {
       $host = 'localhost';
       $user = 'root';
       $pass = '';
       $db = 'sistema_pqrs'; // O el nombre asignado a tu BD local

       $conexion = mysqli_connect($host, $user, $pass, $db);
       if (!$conexion) {
           die("Error de conexión: " . mysqli_connect_error());
       }
       return $conexion;
   }
   ```
3. Asegúrate de tener instalado [Composer](https://getcomposer.org/) y ejecuta la instalación de dependencias en la raíz del proyecto:
   ```bash
   composer install
   ```
4. Abre tu navegador e ingresa a `http://localhost/PROYECTO_PQRS/`.

### Opción 2: Usando Docker de forma Local
Si cuentas con Docker en tu equipo, puedes construir y probar el contenedor localmente:
1. Construye la imagen del contenedor:
   ```bash
   docker build -t sistema-pqrs .
   ```
2. Ejecuta el contenedor exponiendo el puerto deseado (ej. 8080):
   ```bash
   docker run -d -p 8080:80 --name pqrs-app sistema-pqrs
   ```
3. Ingresa a tu navegador en `http://localhost:8080/`.

---

## ☁️ Despliegue en la Nube (Railway)

Este proyecto incluye soporte directo para desplegarse en [Railway](https://railway.app) a través de Docker de forma automática:
1. **Base de Datos:** Railway aprovisiona un servicio MySQL. El esquema de `BD.txt` debe ejecutarse sobre el servicio de base de datos MySQL creado.
2. **Configuración de Conexión:** En el archivo `config/conexion.php` ya está definida la conexión interna segura para Railway (`mysql.railway.internal`).
3. **Servidor Web Caddy:** El `Dockerfile` instala PHP 8.2 y configura **Caddy Server** automáticamente. Caddy redirige de forma dinámica el puerto de escucha definido por Railway (`$PORT`), gestiona las rutas seguras de PHP FastCGI y realiza la reescritura de URL sin necesidad de archivos `.htaccess`.

---

## 🔑 Credenciales de Acceso por Defecto

Para propósitos de prueba e inicialización en entornos de desarrollo, el script de la base de datos registra la siguiente credencial de administrador:

* **URL de Acceso Administrativo:** `http://localhost/PROYECTO_PQRS/administrador/login.php`
* **Nombre de Usuario (Usuario):** `admin`
* **Contraseña:** `1118367962`

> [!IMPORTANT]
> **Recomendación de Seguridad:** Se aconseja encarecidamente cambiar la contraseña y el nombre de usuario por defecto una vez desplegado el sistema en entornos públicos o de producción para proteger la integridad de los datos de los ciudadanos.
