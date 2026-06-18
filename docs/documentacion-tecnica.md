# Documentación Técnica — Sistema de Gestión de PQRS

**Versión:** 1.1  
**Fecha:** Junio 2026  
**Tecnología:** PHP 8.2 (PDO, PSR-4) · MySQL · Vanilla CSS · PHPMailer · DomPDF · Chart.js

---

## Tabla de Contenidos

1. [Resumen del Sistema](#1-resumen-del-sistema)
2. [Arquitectura General](#2-arquitectura-general)
3. [Estructura de Carpetas](#3-estructura-de-carpetas)
4. [Base de Datos](#4-base-de-datos)
5. [Módulos del Sistema](#5-módulos-del-sistema)
6. [Flujo Principal](#6-flujo-principal)
7. [Seguridad](#7-seguridad)
8. [Correo Electrónico (PHPMailer)](#8-correo-electrónico-phpmailer)
9. [Generación de PDF y Excel](#9-generación-de-pdf-y-excel)
10. [Requisitos Funcionales Implementados](#10-requisitos-funcionales-implementados)
11. [Instalación](#11-instalación)
12. [Variables de Sesión](#12-variables-de-sesión)
13. [Configuración del Sistema](#13-configuración-del-sistema)

---

## 1. Resumen del Sistema

El **Sistema de Gestión de PQRS** es una aplicación web desarrollada en PHP para la recepción, seguimiento y resolución de Peticiones, Quejas, Reclamos, Sugerencias y Denuncias, cumpliendo con la normativa colombiana:

- **Ley 1755 de 2015** — Derecho fundamental de petición
- **Ley 1437 de 2011** — Código de Procedimiento Administrativo
- **Ley 1474 de 2011** — Estatuto Anticorrupción
- **Ley 1581 de 2012** — Protección de datos personales

El sistema opera en dos portales:

| Portal | Usuarios | Acceso |
|--------|----------|--------|
| Portal Ciudadano | Ciudadanos en general | Público, sin registro |
| Panel Administrativo | Gestores / Administradores | Login con credenciales |

---

## 2. Arquitectura General

El proyecto sigue una arquitectura **MVC (Modelo-Vista-Controlador)** robusta con **Principios SOLID**, utilizando un Controlador Frontal. 
- Los **Controladores** coordinan el flujo.
- Los **Modelos** manejan exclusivamente los datos mediante **PDO (PHP Data Objects)**.
- Los **Servicios** abstraen utilidades de terceros (ej. PHPMailer).
- Las **Vistas** solo muestran HTML.

```
Navegador ──► index.php?ruta=... 
                    │
                    ├── app/core/Container.php                (DI Container)
                    ├── app/controllers/PqrsController.php    (Portal ciudadano)
                    ├── app/controllers/admin/AuthController.php    (Auth)
                    ├── app/controllers/admin/DashboardController.php
                    ├── app/controllers/admin/PqrsController.php
                    │
                    ├── app/models/                           (Capa de Datos PDO)
                    │        ├── Database.php                 (Singleton)
                    │        ├── PqrsModel.php
                    │        └── UsuarioModel.php
                    │
                    ├── app/services/                         (Capa de Servicios)
                    │        └── EmailService.php
                    │
                    └── app/views/                            (UI / HTML puro)
```

**Patrón de URLs (Enrutamiento Frontal):**
```
http://localhost/PROYECTO_PQRS/                          → Inicio
http://localhost/PROYECTO_PQRS/index.php?ruta=pqrs/tipos → Selección de tipo
http://localhost/PROYECTO_PQRS/index.php?ruta=admin/login → Login admin
http://localhost/PROYECTO_PQRS/index.php?ruta=admin/dashboard → Dashboard
```

---

## 3. Estructura de Carpetas

```text
PROYECTO_PQRS/
│
├── app/                        # Arquitectura MVC
│   ├── core/                   # Componentes base (Contenedor DI)
│   │   └── Container.php       # Autowiring vía Reflection
│   ├── controllers/            # Controladores públicos
│   │   ├── HomeController.php
│   │   ├── PqrsController.php
│   │   └── admin/              # Controladores privados (SRP)
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── ConfigController.php
│   │       ├── PqrsController.php
│   │       └── ReportController.php
│   │
│   ├── models/                 # Lógica de datos (Consultas PDO)
│   │   ├── Database.php        # Conexión Singleton a MySQL
│   │   ├── PqrsModel.php       # Consultas sobre PQRS
│   │   ├── AdminModel.php      # Consultas sobre admins
│   │   ├── ConfiguracionModel.php
│   │   └── UsuarioModel.php    # Consultas sobre ciudadanos
│   │
│   ├── services/               # Clases utilitarias aisladas
│   │   └── EmailService.php    # Envío de correos PHPMailer
│   │
│   └── views/                  # Vistas separadas por módulos (Solo HTML)
│
├── config/
│   ├── conexion.php            # Archivo antiguo legacy

│   └── email_config.php        # Credenciales SMTP (no commitear)
│
├── public/                     # Recursos públicos
│   └── css/                    # Estilos unificados con variables CSS
│       └── estilos.css
│
├── docs/                       # Documentación del proyecto
│   ├── documentacion-tecnica.md
│   └── manual-usuario.md
│
├── uploads/                    # Archivos adjuntos subidos
├── logs/                       # Log de correos enviados
├── vendor/                     # Dependencias Composer (PHPMailer, DomPDF)
├── BD.txt                      # Script SQL completo
├── composer.json
└── index.php                   # Controlador Frontal principal
```

---

## 4. Base de Datos

**Nombre sugerido:** `sistema_pqrs`

### Diagrama de relaciones

```
administrador ──< pqrs >── usuario
administrador ──< historial_accion >── pqrs
administrador ──< reporte
configuracion_sistema (tabla singleton, id=1)
```

### Tablas

#### `administrador`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | Auto-incremento |
| nombre_usuario | VARCHAR(50) UK | Login único |
| contrasena | VARCHAR(255) | Hash bcrypt |
| nombre_completo | VARCHAR(150) | Nombre del funcionario |
| correo_electronico | VARCHAR(150) | Correo para notificaciones |
| rol | VARCHAR(50) | `ADMIN` / `SUPERADMIN` |
| estado | ENUM | `activo` / `inactivo` |
| ultimo_acceso | DATETIME | Último login |
| token_recuperacion | VARCHAR(64) | Token de 64 chars (hex) |
| token_expiracion | DATETIME | Expira en 1 hora |

#### `usuario`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | Auto-incremento |
| tipo_persona | VARCHAR(50) | `natural` / `juridica` / `anonima` |
| nombre_completo | VARCHAR(150) | Solo persona natural |
| documento_identidad | VARCHAR(50) | CC, CE, TI, PAS |
| tipo_documento | VARCHAR(20) | Tipo de documento |
| correo_electronico | VARCHAR(150) | Para notificaciones |
| telefono | VARCHAR(20) | Contacto |
| razon_social | VARCHAR(150) | Solo jurídica |
| nit | VARCHAR(50) | Solo jurídica |
| nombre_representante | VARCHAR(150) | Solo jurídica |
| correo_corporativo | VARCHAR(150) | Solo jurídica |
| fecha_registro | DATETIME | Auto-fill |

#### `pqrs`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | Auto-incremento |
| codigo_radicado | VARCHAR(50) UK | `PQRS-AAAA-MM-NNN` |
| tipo_solicitud | VARCHAR(50) | `peticion`, `queja`, `reclamo`, `sugerencia`, `denuncia` |
| asunto | VARCHAR(255) | Título breve |
| descripcion | TEXT | Detalle completo |
| archivo_adjunto | VARCHAR(255) | Nombre del archivo en `/uploads/` |
| estado | VARCHAR(50) | `PENDIENTE`, `EN_PROCESO`, `RESUELTO`, `RECHAZADO` |
| fecha_radicacion | DATETIME | Auto-fill |
| fecha_actualizacion | DATETIME | ON UPDATE |
| fecha_vencimiento | DATE | Calculada según tipo |
| respuesta_administrador | TEXT | Respuesta visible al ciudadano |
| fecha_respuesta | DATETIME | Cuándo se respondió |
| desea_notificacion | TINYINT(1) | 1 = sí, 0 = no |
| usuario_id | BIGINT FK | → usuario.id |
| administrador_id | BIGINT FK | → administrador.id |

#### `historial_accion`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | Auto-incremento |
| pqrs_id | BIGINT FK | → pqrs.id |
| administrador_id | BIGINT FK | → administrador.id |
| accion_realizada | VARCHAR(100) | `CAMBIO_ESTADO`, `RESPUESTA`, `VISUALIZACION`, etc. |
| estado_anterior | VARCHAR(50) | Estado antes del cambio |
| estado_nuevo | VARCHAR(50) | Estado después del cambio |
| descripcion | TEXT | Detalle adicional |
| fecha_hora | DATETIME | Timestamp del evento |

#### `reporte`
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | Auto-incremento |
| fecha_generacion | DATETIME | Cuándo se generó |
| tipo_reporte | VARCHAR(100) | `GENERAL`, `PETICION`, etc. |
| fecha_inicio | DATE | Rango inicio |
| fecha_fin | DATE | Rango fin |
| total_recibidas | INTEGER | Total en el período |
| total_resueltas | INTEGER | Resueltas en el período |
| total_pendientes | INTEGER | Pendientes + en proceso |
| total_rechazadas | INTEGER | Rechazadas en el período |
| tiempo_promedio_respuesta | DOUBLE | En días |
| porcentaje_cumplimiento | DOUBLE | % resueltas a tiempo |
| formato_exportacion | VARCHAR(20) | `WEB`, `PDF`, `EXCEL` |
| administrador_id | BIGINT FK | Quién generó el reporte |

#### `configuracion_sistema` (singleton id=1)
| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | BIGINT PK | Siempre 1 |
| dias_vencimiento_peticion | INTEGER | Default: 15 |
| dias_vencimiento_queja | INTEGER | Default: 15 |
| dias_vencimiento_reclamo | INTEGER | Default: 15 |
| dias_vencimiento_sugerencia | INTEGER | Default: 15 |
| dias_vencimiento_denuncia | INTEGER | Default: 15 |
| correo_notificaciones | VARCHAR(150) | Correo institucional |
| nombre_empresa | VARCHAR(150) | Nombre de la entidad |

---

## 5. Módulos del Sistema

### 5.1 Portal Ciudadano (app/views/pqrs/ y PqrsController)

| Archivo / Acción | Función |
|---------|---------|
| `HomeController->index()` | Página informativa con botones de acción principal |
| `PqrsController->tipos()` | 5 tarjetas visuales para seleccionar el tipo de PQRS |
| `PqrsController->formulario()` | Formulario dinámico de radicación y guardado |
| `PqrsController->confirmacion()` | Pantalla de confirmación con código radicado |
| `PqrsController->consulta()` | Búsqueda y visualización de PQRS por código o correo |

### 5.2 Panel Administrativo (app/views/admin/ y AdminController)

| Archivo / Acción | Función |
|---------|---------|
| `AdminController->dashboard()` | KPIs, estadísticas y acceso rápido |
| `AdminController->pqrs()` | Bandeja con filtros por estado, tipo, fecha y búsqueda |
| `AdminController->pqrs_ver()` | Detalle completo + historial reciente + cambio de estado |
| `AdminController->pqrs_responder()`| Mostrar vista del editor de respuesta |
| `AdminController->guardar_respuesta()`| Procesa el formulario, actualiza DB y envía notificación |
| `AdminController->pqrs_cambiar_estado()` | Procesa el POST para cambiar estado y registrar historial |
| `AdminController->pqrs_historial()` | Timeline cronológico de todas las acciones |
| `AdminController->reportes()` | Métricas + gráficos Chart.js + exportación |
| `AdminController->configuracion()` | Perfil del admin + parámetros del sistema |
| `AdminController->exportar_pdf()` | Exportación de reportes a PDF |
| `AdminController->exportar_excel()`| Exportación de reportes a Excel |

---

## 6. Flujo Principal

### Flujo ciudadano — Radicar PQRS

```
index.php?ruta=home/index
  → [Botón "Nueva Solicitud"]
  → modal_terminos.php (aceptación obligatoria)
  → index.php?ruta=pqrs/tipos (seleccionar tipo)
  → index.php?ruta=pqrs/formulario (GET: mostrar, POST: procesar en PqrsController)
      ├── Insertar en tabla `usuario`
      ├── Generar código PQRS-AAAA-MM-NNN
      ├── Calcular fecha_vencimiento
      ├── Guardar archivo adjunto en /uploads/
      ├── Insertar en tabla `pqrs`
      └── Enviar correo con PHPMailer
  → index.php?ruta=pqrs/confirmacion (mostrar código + estado correo)
```

### Flujo ciudadano — Consultar estado

```
index.php?ruta=home/index
  → [Botón "Consultar Estado"]
  → index.php?ruta=pqrs/consulta
      ├── Búsqueda por código o correo
  → Mostrar: estado, fechas, descripción, barra de progreso, respuesta
```

### Flujo administrador — Gestionar PQRS

```
index.php?ruta=admin/login → index.php?ruta=admin/dashboard
  → index.php?ruta=admin/pqrs (bandeja con filtros)
  → index.php?ruta=admin/pqrs_ver&id=X (ver detalle)
      ├── index.php?ruta=admin/pqrs_responder (redactar respuesta + notificar ciudadano)
      └── index.php?ruta=admin/pqrs_cambiar_estado (POST: cambiar estado)
  → index.php?ruta=admin/pqrs_historial&id=X (ver trazabilidad completa)
```

### Generación del código radicado

```php
// Obtener el máximo consecutivo del mes actual
SELECT MAX(CAST(SUBSTRING(codigo_radicado, -3) AS UNSIGNED)) as max_num
FROM pqrs
WHERE YEAR(fecha_radicacion) = $anio AND MONTH(fecha_radicacion) = $mes

// Formato final
$codigo = "PQRS-{$anio}-{$mes}-{$consecutivo}";
// Ejemplo: PQRS-2026-06-001
```

---

## 7. Seguridad

### Autenticación y sesión
- Sesión PHP con expiración por inactividad de **30 minutos**
- `verificar_sesion.php` actúa como middleware en todas las páginas del panel admin
- Las contraseñas se almacenan con `password_hash($pass, PASSWORD_BCRYPT)`
- Verificación con `password_verify()` o comparación directa (compatibilidad retroactiva)

### Recuperación de contraseña
1. Se genera `bin2hex(random_bytes(32))` → 64 caracteres hexadecimales
2. Se almacena en `administrador.token_recuperacion` con expiración de 1 hora
3. El enlace enviado al correo apunta a `restablecer_contrasena.php?token=...`
4. Al restablecer exitosamente el token se anula: `token_recuperacion = NULL`

### Protección SQL
- Todas las consultas con datos de usuario usan **Prepared Statements** (`mysqli_prepare`)
- Los filtros de reportes usan `mysqli_real_escape_string` para strings en WHERE dinámicos
- Validación de tipos en PHP antes de insertar (intval, trim, filter_var)

### XSS
- Toda salida a HTML usa `htmlspecialchars()`
- Archivos adjuntos: solo se guarda el nombre, nunca se ejecuta el contenido

### Subida de archivos
- Extensiones permitidas: `.pdf`, `.doc`, `.docx`, `.jpg`, `.jpeg`, `.png`
- El nombre del archivo se renombra con timestamp: `time() . '_' . basename($name)`

---

## 8. Correo Electrónico (PHPMailer)

El sistema usa PHPMailer con SMTP. La configuración está en `config/email_config.php`:

```php
return [
    'smtp_host'       => 'smtp.gmail.com',
    'smtp_port'       => 587,
    'smtp_encryption' => 'tls',
    'smtp_user'       => 'tu@correo.com',
    'smtp_password'   => 'contraseña_de_aplicacion',
    'from_email'      => 'tu@correo.com',
    'from_name'       => 'Sistema PQRS',
];
```

**Correos que envía el sistema:**

| Evento | Destinatario | Archivo |
|--------|-------------|---------|
| Confirmación de radicación | Ciudadano | `pqrs/formulario.php` |
| Respuesta del administrador | Ciudadano | `administrador/pqrs_responder.php` |
| Recuperación de contraseña | Administrador | `administrador/recuperar_contrasena.php` |

Los intentos de envío se registran en `logs/email_log.txt`.

---

## 9. Generación de PDF y Excel

### PDF — DomPDF
```php
require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('Reporte.pdf', ['Attachment' => true]);
```

### Excel — HTML→XLS
El sistema genera un archivo `.xls` usando tablas HTML con cabeceras MIME específicas. Compatible con Microsoft Excel y LibreOffice Calc.

---

## 10. Requisitos Funcionales Implementados

| RF | HU | Descripción | Archivo |
|----|----|-------------|---------|
| RF01 | HU-01 | Página informativa | `index.php` |
| RF02 | HU-01 | Botón Nueva Solicitud | `index.php` |
| RF03 | HU-01 | Botón Consultar Estado + Login Admin | `index.php`, `header.php` |
| RF04 | HU-02 | Modal Términos de Uso | `modal_terminos.php` |
| RF05 | HU-02 | Aceptación obligatoria | `modal_terminos.php` |
| RF06 | HU-03 | 5 tarjetas de tipo con icono y descripción | `pqrs/tipos.php` |
| RF07-08 | HU-04 | Formulario dinámico por tipo de persona | `pqrs/formulario.php` |
| RF09 | HU-04 | Campos según perfil + validaciones tiempo real | `pqrs/formulario.php` |
| RF10 | HU-05 | Código PQRS-AAAA-MM-NNN consecutivo mensual | `pqrs/formulario.php` |
| RF11 | HU-05 | Confirmación + correo al ciudadano | `pqrs/confirmacion.php` |
| RF12-14 | HU-11 | Consulta por código o correo | `pqrs/consulta_pqrs.php` |
| RF15 | HU-06 | Login con sesión activa + expiración | `administrador/login.php` |
| RF16 | HU-07 | Bandeja de solicitudes con tabla | `administrador/pqrs.php` |
| RF17 | HU-07 | Filtros por estado, tipo, fechas | `administrador/pqrs.php` |
| RF18 | HU-08 | Detalle, cambio de estado, respuesta | `administrador/pqrs_ver.php` |
| RF19 | HU-13 | Historial cronológico de acciones | `administrador/pqrs_historial.php` |
| RF20 | HU-14 | Reportes + gráficos + exportación | `administrador/reportes.php` |
| RF21 | HU-12 | Alertas de vencimiento por urgencia | `administrador/alertas.php` |
| RF22 | HU-09 | Configuración de perfil del admin | `administrador/configuracion.php` |
| RF23 | HU-10 | Recuperación de contraseña por token | `administrador/recuperar_contrasena.php` |
| RF24 | HU-15 | Configuración del sistema (días, empresa) | `administrador/configuracion.php` |

---

## 11. Instalación

### Requisitos previos
- PHP 8.0 o superior con extensiones: `mysqli`, `openssl`, `mbstring`, `fileinfo`
- MySQL 5.7 / MariaDB 10.3 o superior
- Composer
- Servidor web: XAMPP, Laragon, WampServer o similar

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/Santiago072/SistemaPQRS.git PROYECTO_PQRS

# 2. Mover a la carpeta web root (XAMPP)
# C:\xampp\htdocs\PROYECTO_PQRS

# 3. Instalar dependencias PHP
composer install

# 4. Crear la base de datos
mysql -u root -p -e "CREATE DATABASE sistema_pqrs;"
mysql -u root -p sistema_pqrs < BD.txt

# 5. Configurar conexión en config/conexion.php
# Editar $host, $user, $pass, $db según tu entorno

# 6. (Opcional) Configurar correo en config/email_config.php
# Usar contraseña de aplicación de Gmail o credenciales SMTP propias

# 7. Abrir en el navegador
# http://localhost/PROYECTO_PQRS/
```

### Credenciales por defecto
```
Usuario: admin
Contraseña: 1118367962
```
> ⚠️ Cambia estas credenciales inmediatamente desde `Configuración > Mi Perfil` al iniciar por primera vez.

---

## 12. Variables de Sesión

Las siguientes variables están disponibles en todas las páginas protegidas tras incluir `verificar_sesion.php`:

| Variable | Tipo | Contenido |
|----------|------|-----------|
| `$adminId` | int | ID del administrador logueado |
| `$adminUsuario` | string | Nombre de usuario (login) |
| `$adminNombre` | string | Nombre completo |
| `$adminCorreo` | string | Correo electrónico |
| `$adminRol` | string | Rol asignado (`ADMIN`, `SUPERADMIN`) |

---

## 13. Configuración del Sistema

Accesible desde el header (botón **Configuración**) o la URL directa `administrador/configuracion.php`.

La página tiene dos pestañas:

**Tab "Mi Perfil"**
- Modificar nombre completo y correo electrónico
- Cambiar contraseña (requiere ingresar la actual)
- El nombre de usuario no es editable

**Tab "Sistema"**
- Días de vencimiento por tipo de solicitud (1–30 días)
- Nombre de la empresa o entidad
- Correo de notificaciones internas
- Vista previa en tiempo real de los términos al editar

Los cambios se persisten en la tabla `configuracion_sistema` (registro único `id=1`).
