# Manual de Usuario — Sistema de Gestión de PQRS

**Versión:** 1.0  
**Fecha:** Junio 2026

---

## Tabla de Contenidos

1. [Introducción](#1-introducción)
2. [Acceso al Sistema](#2-acceso-al-sistema)
3. [Portal del Ciudadano](#3-portal-del-ciudadano)
   - 3.1 [Radicar una PQRS](#31-radicar-una-pqrs)
   - 3.2 [Consultar el estado de una solicitud](#32-consultar-el-estado-de-una-solicitud)
4. [Panel de Administración](#4-panel-de-administración)
   - 4.1 [Iniciar sesión](#41-iniciar-sesión)
   - 4.2 [Dashboard — Panel de Control](#42-dashboard--panel-de-control)
   - 4.3 [Bandeja de Solicitudes](#43-bandeja-de-solicitudes)
   - 4.4 [Ver detalle de una PQRS](#44-ver-detalle-de-una-pqrs)
   - 4.5 [Responder una PQRS](#45-responder-una-pqrs)
   - 4.6 [Historial de Acciones](#46-historial-de-acciones)
   - 4.7 [Centro de Alertas](#47-centro-de-alertas)
   - 4.8 [Reportes](#48-reportes)
   - 4.9 [Configuración](#49-configuración)
5. [Recuperar Contraseña](#5-recuperar-contraseña)
6. [Estados de una PQRS](#6-estados-de-una-pqrs)
7. [Tiempos Legales de Respuesta](#7-tiempos-legales-de-respuesta)
8. [Preguntas Frecuentes](#8-preguntas-frecuentes)

---

## 1. Introducción

El **Sistema de Gestión de PQRS** es una plataforma web que permite a los ciudadanos radicar Peticiones, Quejas, Reclamos, Sugerencias y Denuncias de forma fácil y segura, y a los administradores gestionar, responder y hacer seguimiento de cada solicitud.

El sistema garantiza:
- Un **código único de radicado** para cada solicitud
- **Notificación por correo** al radicar y al recibir respuesta
- **Consulta en tiempo real** del estado sin necesidad de crear una cuenta
- **Trazabilidad completa** de cada acción realizada sobre una solicitud
- **Cumplimiento de los términos legales** establecidos por la Ley 1755 de 2015

---

## 2. Acceso al Sistema

El sistema se divide en dos secciones:

| Sección | URL | Quién la usa |
|---------|-----|--------------|
| Portal Ciudadano | `http://localhost/PROYECTO_PQRS/` | Ciudadanos en general |
| Panel Admin | `http://localhost/PROYECTO_PQRS/index.php?ruta=admin/login` | Gestores / Administradores |

> Si el sistema está en producción, reemplace `localhost/PROYECTO_PQRS` por la URL real que le indique el administrador.

---

## 3. Portal del Ciudadano

### 3.1 Radicar una PQRS

#### Paso 1 — Página de inicio

Al ingresar al sistema verá la página de inicio con información sobre los tipos de PQRS, el proceso de radicación y los tiempos legales de respuesta.

Haga clic en el botón **"Nueva Solicitud"**.

#### Paso 2 — Términos de uso

Se abrirá un modal con el marco legal aplicable. Lea los términos y marque la casilla **"He leído y acepto los términos de uso"**. Sin aceptar no puede continuar.

Haga clic en **"Continuar al Formulario"**.

#### Paso 3 — Seleccionar tipo de solicitud

Verá 5 tarjetas. Elija el tipo que corresponda a su caso:

| Tipo | Cuándo usarlo |
|------|---------------|
| **Petición** | Cuando desea solicitar información, documentos o acciones a una entidad |
| **Queja** | Cuando está inconforme con la conducta de un funcionario o la atención recibida |
| **Reclamo** | Cuando exige el cumplimiento de un derecho o denuncia un mal servicio |
| **Sugerencia** | Cuando quiere proponer mejoras en los procesos o servicios |
| **Denuncia** | Cuando conoce posibles irregularidades o actos de corrupción |

Haga clic sobre la tarjeta del tipo que corresponda y luego en **"Continuar al Formulario"**.

#### Paso 4 — Completar el formulario

El formulario se adapta según el tipo de persona que usted sea:

**Persona Natural**
Complete los siguientes campos:
- Nombre completo *(obligatorio)*
- Tipo de documento (CC, CE, TI, Pasaporte) *(obligatorio)*
- Número de documento *(obligatorio)*
- Teléfono *(obligatorio)*
- Correo electrónico *(obligatorio — para recibir notificaciones)*
- Asunto *(obligatorio — resumen breve en máx. 250 caracteres)*
- Descripción detallada *(obligatorio)*
- Archivo adjunto *(opcional — PDF, Word, JPG, PNG, máx. 5MB)*

**Persona Jurídica**
Complete los siguientes campos:
- Razón social *(obligatorio)*
- NIT *(obligatorio)*
- Nombre del representante legal *(obligatorio)*
- Correo corporativo *(obligatorio)*
- Teléfono *(obligatorio)*
- Asunto *(obligatorio)*
- Descripción detallada *(obligatorio)*
- Archivo adjunto *(opcional)*

**Anónima**
Solo complete:
- Asunto *(obligatorio)*
- Descripción detallada *(obligatorio)*
- Archivo adjunto *(opcional)*

> ⚠️ Si radica de forma anónima **no** recibirá notificaciones por correo. Guarde el código de radicado que se generará para consultar el estado.

Marque o desmarque la casilla **"Deseo recibir notificación por correo"** según su preferencia.

Haga clic en **"Enviar Solicitud"**.

#### Paso 5 — Confirmación

Si el formulario fue enviado correctamente verá la pantalla de confirmación con:

- **Código de radicado** en formato `PQRS-AAAA-MM-NNN`  
  *(Ejemplo: PQRS-2026-06-001)*
- Tipo de solicitud, estado actual y fecha límite de respuesta
- Botón para **copiar el código** al portapapeles
- Indicador de si el correo de confirmación fue enviado

> 📌 **Guarde su código de radicado.** Lo necesitará para consultar el estado de su solicitud.

---

### 3.2 Consultar el estado de una solicitud

Desde la página de inicio haga clic en **"Consultar Estado"**.

Verá dos opciones de búsqueda:

**Por código de radicado**
Ingrese el código exacto con el formato `PQRS-AAAA-MM-NNN` y haga clic en **"Consultar"**.

**Por correo electrónico**
Ingrese el correo con el que registró su solicitud y haga clic en **"Buscar"**. Se mostrarán todas las PQRS asociadas a ese correo.

#### Información que verá

Para cada solicitud encontrada:
- Código de radicado y fecha de radicación
- **Estado actual** con barra de progreso visual
- Tipo de solicitud y asunto
- Fecha límite de respuesta y días restantes
- **Respuesta del administrador** (cuando esté disponible)
- Archivo adjunto (si adjuntó documentos, puede verlos o descargarlos)

---

## 4. Panel de Administración

### 4.1 Iniciar sesión

Ingrese a `index.php?ruta=admin/login` y complete:
- **Usuario:** su nombre de usuario asignado
- **Contraseña:** su contraseña

Haga clic en **"Iniciar Sesión"**.

> La sesión expira automáticamente después de **30 minutos** de inactividad.

Si olvidó su contraseña, haga clic en **"¿Olvidó su contraseña?"** ([ver sección 5](#5-recuperar-contraseña)).

---

### 4.2 Dashboard — Panel de Control

Al iniciar sesión verá el tablero principal con:

**Tarjetas de estadísticas**
- Total PQRS registradas
- Pendientes / En Proceso / Resueltas
- PQRS vencidas
- PQRS radicadas este mes

**Acceso Rápido**
Tres accesos directos: Bandeja PQRS, Centro de Alertas y Reportes.

**Últimas solicitudes**
Tabla con las 5 PQRS más recientes. Desde ahí puede ir directamente al detalle o a responder.

**Botones del header**
En la parte superior derecha encontrará:
- **Configuración** — para editar su perfil y los parámetros del sistema
- **Cerrar Sesión** — para salir de forma segura

---

### 4.3 Bandeja de Solicitudes

Acceda desde el acceso rápido o la URL `index.php?ruta=admin/pqrs`.

#### Filtros disponibles
- **Buscar:** por código de radicado, asunto o nombre del solicitante
- **Estado:** Pendiente / En Proceso / Resuelto / Rechazado
- **Tipo:** Petición / Queja / Reclamo / Sugerencia / Denuncia
- **Desde / Hasta:** rango de fechas de radicación
- **Ordenar:** más recientes, más antiguos, por vencimiento o por código

Haga clic en **"Filtrar"** para aplicar y en **"Limpiar"** para restablecer.

#### Tabla de solicitudes

Cada fila muestra: Código · Tipo · Tipo Persona · Asunto · Solicitante · Fecha · Vencimiento · Estado

Las filas se colorean según la urgencia:
- 🔴 **Rojo** — vencidas o vencen en menos de 5 días
- 🟡 **Amarillo** — vencen entre 6 y 10 días
- 🔵 **Azul** — vencen entre 11 y 15 días

#### Acciones por PQRS
- 👁️ **Ver** — abre el detalle completo
- ↩️ **Responder** — abre el formulario de respuesta
- 🕐 **Historial** — abre la línea de tiempo de acciones

La bandeja incluye **paginación** (15 solicitudes por página).

---

### 4.4 Ver detalle de una PQRS

Haga clic en el ícono 👁️ de cualquier solicitud.

Verá:
- Encabezado con código, tipo, estado y urgencia de vencimiento
- **Información de la solicitud:** asunto, descripción completa y archivo adjunto
- **Datos del solicitante:** según tipo de persona (Natural, Jurídica o Anónima)
- **Respuesta oficial** (si ya fue respondida)
- **Cambiar Estado** — formulario lateral para actualizar el estado con comentario opcional
- **Historial reciente** — últimas 5 acciones registradas

#### Cambiar estado desde el detalle

En el panel lateral "Cambiar Estado":
1. Seleccione el nuevo estado en el desplegable
2. Escriba un comentario opcional
3. Haga clic en **"Actualizar Estado"**

Las transiciones permitidas son:
```
PENDIENTE → EN PROCESO → RESUELTO
PENDIENTE → RECHAZADO
EN PROCESO → RESUELTO
EN PROCESO → RECHAZADO
```
> Una vez **Resuelto** o **Rechazado**, el estado no puede cambiarse.

---

### 4.5 Responder una PQRS

Haga clic en el ícono ↩️ de cualquier solicitud, o en el botón **"Responder"** desde el detalle.

1. Redacte la respuesta formal en el campo de texto
2. Seleccione si desea cambiar el estado (opcional)
3. Marque **"Hacer visible al ciudadano"** si quiere que el ciudadano vea la respuesta en la consulta pública
4. Haga clic en **"Enviar Respuesta"**

Si el solicitante tiene correo registrado y la respuesta es pública, el sistema enviará automáticamente una notificación por correo con la respuesta.

---

### 4.6 Historial de Acciones

Haga clic en el ícono 🕐 de cualquier solicitud, o en **"Historial completo"** desde el detalle.

Verá una línea de tiempo cronológica (más reciente primero) con cada acción registrada:
- Fecha y hora
- Tipo de acción (VISUALIZACIÓN, CAMBIO_ESTADO, RESPUESTA, etc.)
- Descripción del cambio
- Cambio de estado (estado anterior → estado nuevo)
- Nombre del administrador que realizó la acción

Al final siempre aparece el evento de creación de la solicitud.

---

### 4.7 Centro de Alertas

Acceda desde el acceso rápido o `index.php?ruta=admin/alertas`.

Las PQRS con estado **PENDIENTE** o **EN PROCESO** se agrupan en 4 niveles:

| Nivel | Criterio | Color |
|-------|----------|-------|
| **Vencidas** | Fecha de vencimiento ya pasó | 🔴 Rojo oscuro |
| **Crítico** | Vencen en 0–5 días | 🔴 Rojo |
| **Urgente** | Vencen en 6–10 días | 🟡 Naranja |
| **Moderado** | Vencen en 11–15 días | 🟢 Verde |

Desde cada alerta puede ir directamente a **Ver** o **Responder** la solicitud.

En la bandeja principal (`index.php?ruta=admin/pqrs`) también aparecen banners de alerta en la parte superior cuando hay solicitudes próximas a vencer.

---

### 4.8 Reportes

Acceda desde el acceso rápido o `index.php?ruta=admin/reportes`.

#### Filtros
- **Desde / Hasta:** rango de fechas de radicación
- **Tipo:** filtrar por tipo de solicitud específico

Haga clic en **"Generar Reporte"** para actualizar.

#### Métricas mostradas
- Total recibidas en el período
- Total resueltas
- Pendientes (incluye en proceso)
- Tiempo promedio de respuesta en días

#### Gráficos
- **Distribución por Tipo** — gráfico de dona
- **Distribución por Estado** — gráfico de barras
- **Tendencia Mensual** — gráfico de líneas (últimos 6 meses)

#### Tabla de cumplimiento de términos
Muestra cuántas PQRS fueron resueltas dentro y fuera del término legal, con el porcentaje de cumplimiento.

#### Exportación
- Botón **"Exportar PDF"** — genera un reporte descargable en PDF
- Botón **"Exportar Excel"** — genera un archivo `.xls` con todos los datos

---

### 4.9 Configuración

Acceda desde el botón **"Configuración"** en el header superior o mediante la URL `index.php?ruta=admin/configuracion`.

La página tiene dos pestañas:

#### Pestaña "Mi Perfil"

**Datos Personales**
- Nombre completo
- Correo electrónico
- *(El nombre de usuario no puede modificarse)*

**Cambiar Contraseña**
- Ingrese su contraseña actual
- Ingrese la nueva contraseña (mínimo 6 caracteres)
- Confirme la nueva contraseña
- Si deja los campos en blanco, la contraseña no se modifica

Haga clic en **"Guardar Perfil"**.

#### Pestaña "Sistema"

> Solo los administradores con acceso al panel pueden modificar estos parámetros.

**Días de Vencimiento por Tipo**
Defina cuántos días hábiles tiene la entidad para responder cada tipo de solicitud:
- Petición (default: 15 días)
- Queja (default: 15 días)
- Reclamo (default: 15 días)
- Sugerencia (default: 15 días)
- Denuncia (default: 15 días)

Los valores deben estar entre **1 y 30** días. Se muestra una vista previa en tiempo real.

**Datos Institucionales**
- Nombre de la empresa o entidad
- Correo de notificaciones internas

Haga clic en **"Guardar Configuración"**.

---

## 5. Recuperar Contraseña

Si olvidó su contraseña como administrador:

1. En la página de login, haga clic en **"¿Olvidó su contraseña?"**
2. Ingrese su **correo electrónico registrado** y haga clic en **"Enviar Enlace de Recuperación"**
3. Recibirá un correo con el asunto *"Recuperación de Contraseña - Sistema PQRS"*
4. Abra el correo y haga clic en el botón **"Restablecer Contraseña"**
5. Ingrese su nueva contraseña (mínimo 6 caracteres) y confírmela
6. Haga clic en **"Restablecer Contraseña"**
7. Aparecerá un mensaje de éxito con un enlace al login

> ⚠️ El enlace de recuperación **expira en 1 hora**. Si no lo usa a tiempo, deberá solicitar uno nuevo.  
> Por seguridad, si el correo no está registrado en el sistema, no se mostrará ningún error específico.

---

## 6. Estados de una PQRS

| Estado | Significado | ¿Quién lo asigna? |
|--------|-------------|-------------------|
| **Pendiente** | Recién radicada, sin atender | Sistema (automático) |
| **En Proceso** | El administrador la tomó y está gestionando | Administrador |
| **Resuelto** | Se emitió respuesta formal al ciudadano | Administrador |
| **Rechazado** | No procede; se debe incluir justificación | Administrador |

El ciudadano puede ver el estado actual en cualquier momento desde la sección **"Consultar Estado"** de la página de inicio.

---

## 7. Tiempos Legales de Respuesta

Según la normativa colombiana vigente, los términos estándar son:

| Tipo de Solicitud | Término Legal | Norma |
|-------------------|---------------|-------|
| Petición | 15 días hábiles | Ley 1755 de 2015, Art. 13 |
| Queja | 15 días hábiles | Ley 1755 de 2015, Art. 14 |
| Reclamo | 15 días hábiles | Ley 1437 de 2011, Art. 56 |
| Sugerencia | 15 días hábiles | Ley 1755 de 2015, Art. 15 |
| Denuncia | 10 días hábiles | Ley 1474 de 2011, Art. 7 |

> Estos términos pueden ajustarse desde **Configuración > Sistema** por el administrador dentro del rango permitido de 1 a 30 días.

---

## 8. Preguntas Frecuentes

**¿Necesito crear una cuenta para radicar una PQRS?**  
No. El sistema es completamente público para los ciudadanos. No se requiere registro ni cuenta.

**¿Qué hago si perdí mi código de radicado?**  
Si registró su solicitud con un correo electrónico, puede consultar por correo en **"Consultar Estado"** y el sistema mostrará todas sus solicitudes.

**¿Puedo radicar una queja de forma anónima?**  
Sí. Al seleccionar **"Anónima"** en el formulario, el sistema no le pedirá datos personales. Tenga en cuenta que no recibirá notificaciones por correo; guarde el código de radicado.

**¿Cuándo recibiré respuesta a mi solicitud?**  
Dentro de los términos legales establecidos (ver tabla en sección 7). Puede verificar el estado en cualquier momento con su código de radicado.

**¿Qué formato admite el archivo adjunto?**  
PDF, Word (.doc, .docx), JPG y PNG. El tamaño máximo es de 5 MB.

**No recibí el correo de confirmación. ¿Qué hago?**  
Revise la carpeta de spam. Si el problema persiste, consulte el estado de su PQRS usando su código de radicado en la sección **"Consultar Estado"**. Si el código aparece, su solicitud fue registrada correctamente.

**¿Puedo modificar una PQRS después de enviarla?**  
No. Una vez radicada, la solicitud no puede modificarse. Si cometió un error, puede radicar una nueva solicitud o contactar al administrador.

**Como administrador, ¿puedo volver a abrir una PQRS resuelta?**  
No. Una vez que una PQRS llega al estado **Resuelto** o **Rechazado**, el estado es definitivo y no puede cambiarse.

**La sesión del administrador se cerró sola. ¿Por qué?**  
Por seguridad, la sesión expira automáticamente después de **30 minutos de inactividad**. Vuelva a iniciar sesión para continuar.
