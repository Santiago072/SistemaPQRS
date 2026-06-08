# Documentación Técnica — Sistema de Gestión de PQRS

**Versión:** 1.0  
**Fecha:** Junio 2026  
**Tecnología:** PHP 8.2 · MySQL · Vanilla CSS · PHPMailer · DomPDF · Chart.js

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

El proyecto sigue una arquitectura **MVC simplificada** sin framework, con PHP puro orientado a procedimientos. Cada página PHP actúa como controlador y vista al mismo tiempo. La lógica de negocio compartida se centraliza en `includes/funciones.php`.

```
Navegador ──► index.php / pqrs/*.php         (Portal ciudadano)
           ──► administrador/*.php            (Panel admin)
                    │
                    ├── includes/verificar_sesion.php   (Middleware auth)
                    ├── includes/funciones.php          (Utilidades)
                    ├── config/conexion.php             (BD)
                    └── config/email_config.php         (SMTP)
```

**Patrón de URLs:**
```
http://localhost/PROYECTO_PQRS/                          → Inicio
http://localhost/PROYECTO_PQRS/pqrs/tipos.php            → Selección de tipo
http://localhost/PROYECTO_PQRS/pqrs/formulario.php       → Formulario
http://localhost/PROYECTO_PQRS/pqrs/consulta_pqrs.php    → Consulta ciudadano
http://localhost/PROYECTO_PQRS/administrador/login.php   → Login admin
http://localhost/PROYECTO_PQRS/administrador/dashboard_admin.php → Dashboard
```

---

## 3. Estructura de Carpetas

```text
PROYECTO_PQRS/
│
├── administrador/              # Panel de control administrativo (protegido)
│   ├── dashboard_admin.php     # Tablero con KPIs y acceso rápido
│   ├── login.php               # Autenticación
│   ├── logout.php              # Cierre de sesión
│   ├── recuperar_contrasena.php
│   ├── restablecer_contrasena.php
│   ├── actualizar_perfil.php   # Endpoint AJAX para perfil
│   ├── configuracion.php       # Perfil + configuración del sistema (unificado)
│   ├── pqrs.php                # Bandeja con filtros y paginación
│   ├── pqrs_ver.php            # Detalle completo de una PQRS
│   ├── pqrs_responder.php      # Responder y cambiar estado
│   ├── pqrs_cambiar_estado.php # Cambio rápido de estado
│   ├── pqrs_historial.php      # Timeline de acciones
│   ├── alertas.php             # Centro de alertas de vencimiento
│   ├── reportes.php            # Reportes con gráficos
│   ├── exportar_pdf.php        # Exportación PDF (DomPDF)
│   └── exportar_excel.php      # Exportación Excel (HTML→XLS)
│
├── config/
│   ├── conexion.php            # Función conexion() con MySQLi
│   └── email_config.php        # Credenciales SMTP (no commitear)
│
├── css/
│   └── estilos.css             # Estilos unificados con variables CSS
│
├── docs/                       # Documentación del proyecto
│   ├── documentacion-tecnica.md
│   └── manual-usuario.md
│
├── includes/
│   ├── header.php              # Cabecera común (detecta sesión activa)
│   ├── footer.php              # Pie de página
│   ├── funciones.php           # registrarAccion(), obtenerConfiguracion()
│   ├── verificar_sesion.php    # Middleware: valida sesión e inactividad (30 min)
│   └── modal_terminos.php      # Modal con marco legal y aceptación
│
├── pqrs/                       # Módulos del portal ciudadano
│   ├── tipos.php               # Tarjetas de selección de tipo
│   ├── formulario.php          # Formulario dinámico + POST + envío de correo
│   ├── confirmacion.php        # Pantalla de confirmación con código radicado
│   └── consulta_pqrs.php       # Búsqueda por código o correo
│
├── uploads/                    # Archivos adjuntos subidos
├── logs/                       # Log de correos enviados
├── vendor/                     # Dependencias Composer
├── BD.txt                      # Script SQL completo
├── composer.json
└── index.php                   # Página de inicio
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

### 5.1 Portal Ciudadano

| Archivo | Función |
|---------|---------|
| `index.php` | Página informativa con botones de acción principal |
| `includes/modal_terminos.php` | Modal con leyes aplicables y aceptación obligatoria |
| `pqrs/tipos.php` | 5 tarjetas visuales para seleccionar el tipo de PQRS |
| `pqrs/formulario.php` | Formulario dinámico según tipo de persona + lógica POST |
| `pqrs/confirmacion.php` | Muestra código radicado y estado del correo enviado |
| `pqrs/consulta_pqrs.php` | Búsqueda por código `PQRS-AAAA-MM-NNN` o correo |

### 5.2 Panel Administrativo

| Archivo | Función |
|---------|---------|
| `dashboard_admin.php` | KPIs, estadísticas y acceso rápido |
| `pqrs.php` | Bandeja con filtros por estado, tipo, fecha y búsqueda |
| `pqrs_ver.php` | Detalle completo + historial reciente + cambio de estado |
| `pqrs_responder.php` | Editor de respuesta + notificación por correo |
| `pqrs_cambiar_estado.php` | Endpoint POST para cambiar estado y registrar historial |
| `pqrs_historial.php` | Timeline cronológico de todas las acciones |
| `alertas.php` | PQRS agrupadas por urgencia (0-5, 6-10, 11-15 días) |
| `reportes.php` | Métricas + gráficos Chart.js + exportación |
| `configuracion.php` | Perfil del admin + parámetros del sistema (tabs) |

---

## 6. Flujo Principal

### Flujo ciudadano — Radicar PQRS

```
index.php
  → [Botón "Nueva Solicitud"]
  → modal_terminos.php (aceptación obligatoria)
  → pqrs/tipos.php (seleccionar tipo)
  → pqrs/formulario.php (GET: mostrar, POST: procesar)
      ├── Insertar en tabla `usuario`
      ├── Generar código PQRS-AAAA-MM-NNN
      ├── Calcular fecha_vencimiento (desde configuracion_sistema)
      ├── Guardar archivo adjunto en /uploads/
      ├── Insertar en tabla `pqrs`
      └── Enviar correo con PHPMailer (si notificar=1)
  → pqrs/confirmacion.php (mostrar código + estado correo)
```

### Flujo ciudadano — Consultar estado

```
index.php
  → [Botón "Consultar Estado"]
  → pqrs/consulta_pqrs.php
      ├── Búsqueda por código (consulta exacta)
      └── Búsqueda por correo (lista de todas sus PQRS)
  → Mostrar: estado, fechas, descripción, barra de progreso, respuesta del admin
```

### Flujo administrador — Gestionar PQRS

```
administrador/login.php → dashboard_admin.php
  → pqrs.php (bandeja con filtros)
  → pqrs_ver.php?id=X (ver detalle)
      ├── pqrs_responder.php (redactar respuesta + notificar ciudadano)
      └── pqrs_cambiar_estado.php (POST: cambiar estado)
  → pqrs_historial.php?id=X (ver trazabilidad completa)
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
