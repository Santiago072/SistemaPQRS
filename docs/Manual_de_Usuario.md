# 📘 Manual de Usuario — Sistema de Gestión de PQRS

> **Sistema:** Sistema de Gestión de PQRS para Empresas de Servicios · **Plataforma:** Web (PHP + MySQL)

Este documento describe paso a paso cómo utilizar el Sistema de Gestión de PQRS. Está dirigido a dos tipos de usuarios: **ciudadanos** que desean radicar o consultar solicitudes, y **administradores** que gestionan, responden y hacen seguimiento de cada caso.

---

## Índice

**Portal del Ciudadano**
1. [Acceso al Sistema](#1-acceso-al-sistema)
2. [Radicar una PQRS](#2-radicar-una-pqrs)
3. [Consultar el Estado de una Solicitud](#3-consultar-el-estado-de-una-solicitud)

**Panel de Administración**
4. [Iniciar Sesión como Administrador](#4-iniciar-sesión-como-administrador)
5. [Dashboard — Panel de Control](#5-dashboard--panel-de-control)
6. [Bandeja de Solicitudes](#6-bandeja-de-solicitudes)
7. [Ver Detalle de una PQRS](#7-ver-detalle-de-una-pqrs)
8. [Responder una PQRS](#8-responder-una-pqrs)
9. [Historial de Acciones](#9-historial-de-acciones)
10. [Centro de Alertas](#10-centro-de-alertas)
11. [Reportes de Cumplimiento](#11-reportes-de-cumplimiento)
12. [Configuración del Perfil y del Sistema](#12-configuración-del-perfil-y-del-sistema)
13. [Recuperar Contraseña](#13-recuperar-contraseña)
14. [Cerrar Sesión](#14-cerrar-sesión)
15. [Estados de una PQRS](#15-estados-de-una-pqrs)
16. [Tiempos Legales de Respuesta](#16-tiempos-legales-de-respuesta)
17. [Preguntas Frecuentes](#17-preguntas-frecuentes)

---

## 1. Acceso al Sistema

El sistema está dividido en dos secciones:

| Sección | URL | Quién la usa |
|---------|-----|--------------|
| Portal Ciudadano | `http://localhost/SistemaPQRS/` | Ciudadanos en general |
| Panel Administrador | `http://localhost/SistemaPQRS/index.php?ruta=admin/login` | Gestores / Administradores |

> Si el sistema está en producción, reemplace `localhost/SistemaPQRS` por la URL que le indique el administrador de la plataforma.

Al ingresar al Portal Ciudadano verá la página de inicio con:
- Una explicación de qué es el sistema PQRS y sus tipos.
- El proceso de radicación y los tiempos legales de respuesta.
- El botón **"Nueva Solicitud"** para radicar una PQRS.
- El botón **"Consultar Estado"** para hacer seguimiento a una solicitud existente.
- Un botón discreto **"Administrador"** en la esquina superior para el acceso al panel.

---

## 2. Radicar una PQRS

### Paso 1 — Aceptar los Términos de Uso

Al hacer clic en **"Nueva Solicitud"** se abrirá un modal con el marco legal aplicable. Lea los términos y marque la casilla **"He leído y acepto los términos de uso"**.

> Sin aceptar los términos no puede continuar al formulario.

Haga clic en **"Continuar al Formulario"**.

**Leyes referenciadas en el modal:**
- Ley 1755 de 2015 — Derecho fundamental de petición
- Ley 1437 de 2011 — Código de Procedimiento Administrativo
- Ley 1474 de 2011 — Estatuto Anticorrupción
- Ley 1581 de 2012 — Protección de datos personales

---

### Paso 2 — Seleccionar el Tipo de Solicitud

Verá 5 tarjetas visuales. Seleccione el tipo que corresponda a su caso:

| Tipo | Cuándo usarlo |
|------|---------------|
| **Petición** | Cuando desea solicitar información, documentos o acciones a la entidad |
| **Queja** | Cuando está inconforme con la conducta de un funcionario o la atención recibida |
| **Reclamo** | Cuando exige el cumplimiento de un derecho o denuncia un mal servicio |
| **Sugerencia** | Cuando quiere proponer mejoras en los procesos o servicios |
| **Denuncia** | Cuando conoce posibles irregularidades o actos de corrupción |

Haga clic sobre la tarjeta del tipo que corresponda y luego en **"Continuar al Formulario"**.

---

### Paso 3 — Completar el Formulario

El formulario se adapta según el tipo de persona que usted sea. Seleccione su perfil:

#### Persona Natural
Complete los siguientes campos:
- Nombre completo *(obligatorio)*
- Tipo de documento (CC, CE, TI, Pasaporte) *(obligatorio)*
- Número de documento *(obligatorio)*
- Teléfono *(obligatorio)*
- Correo electrónico *(obligatorio — para recibir notificaciones)*
- Asunto *(obligatorio — resumen breve, máx. 250 caracteres)*
- Descripción detallada *(obligatorio)*
- Archivo adjunto *(opcional — PDF, Word, JPG, PNG, máx. 5 MB)*

#### Persona Jurídica
Complete los siguientes campos:
- Razón social *(obligatorio)*
- NIT *(obligatorio)*
- Nombre del representante legal *(obligatorio)*
- Correo corporativo *(obligatorio)*
- Teléfono *(obligatorio)*
- Asunto *(obligatorio)*
- Descripción detallada *(obligatorio)*
- Archivo adjunto *(opcional)*

#### Anónima
Solo complete:
- Asunto *(obligatorio)*
- Descripción detallada *(obligatorio)*
- Archivo adjunto *(opcional)*

> ⚠️ Si radica de forma anónima **no** recibirá notificaciones por correo. Guarde el código de radicado que se generará para consultar el estado de su solicitud.

Marque o desmarque la casilla **"Deseo recibir notificación por correo"** según su preferencia.

Haga clic en **"Enviar Solicitud"**.

---

### Paso 4 — Pantalla de Confirmación

Si el formulario fue enviado correctamente verá la pantalla de confirmación con:

- **Código de radicado** en formato `PQRS-AAAA-MM-NNN` *(Ejemplo: PQRS-2026-06-001)*
- Tipo de solicitud, estado actual (`Pendiente`) y fecha límite de respuesta
- Botón para **copiar el código** al portapapeles
- Indicador de si el correo de confirmación fue enviado exitosamente

> 📌 **Guarde su código de radicado.** Lo necesitará para consultar el estado de su solicitud en cualquier momento.

---

## 3. Consultar el Estado de una Solicitud

Desde la página de inicio haga clic en **"Consultar Estado"** o acceda directamente a:  
`http://localhost/SistemaPQRS/index.php?ruta=pqrs/consulta`

Verá dos pestañas de búsqueda:

**Por código de radicado**  
Ingrese el código exacto en formato `PQRS-AAAA-MM-NNN` y haga clic en **"Consultar"**.

**Por correo electrónico**  
Ingrese el correo con el que registró su solicitud y haga clic en **"Buscar"**. Se mostrarán todas las PQRS asociadas a ese correo.

### Información que verá por cada solicitud

- Código de radicado y fecha de radicación
- **Estado actual** con barra de progreso visual (Radicada → En Proceso → Resuelta)
- Tipo de solicitud y asunto
- Descripción completa
- Fecha límite de respuesta y días restantes (o indicador de vencimiento)
- **Respuesta del administrador** (cuando esté disponible)
- Botón para ver o descargar el **archivo adjunto** (si adjuntó documentos)

---

## 4. Iniciar Sesión como Administrador

Ingrese a `index.php?ruta=admin/login` o haga clic en el botón **"Administrador"** en la página de inicio.

Complete el formulario:
- **Usuario:** su nombre de usuario asignado
- **Contraseña:** su contraseña

Haga clic en **"Iniciar Sesión"**.

> La sesión expira automáticamente después de **30 minutos** de inactividad por seguridad.

Si olvidó su contraseña, haga clic en **"¿Olvidó su contraseña?"** ([ver sección 13](#13-recuperar-contraseña)).

---

## 5. Dashboard — Panel de Control

Al iniciar sesión verá el tablero principal con:

### Tarjetas de estadísticas (KPIs)
- Total PQRS registradas
- Pendientes
- En Proceso
- Resueltas
- Rechazadas
- PQRS vencidas
- PQRS radicadas en el mes actual

### Acceso Rápido
Tres botones de acceso directo: **Bandeja PQRS**, **Centro de Alertas** y **Reportes**.

### Últimas solicitudes
Tabla con las PQRS más recientes. Desde ahí puede ir directamente al detalle o a responder.

### Header superior
- **Configuración** — editar perfil y parámetros del sistema
- **Cerrar Sesión** — salir de forma segura

---

## 6. Bandeja de Solicitudes

Acceda desde el acceso rápido o la URL `index.php?ruta=admin/pqrs`.

### Filtros disponibles

| Filtro | Opciones |
|--------|----------|
| Buscar | Por código, asunto o nombre del solicitante |
| Estado | Pendiente / En Proceso / Resuelto / Rechazado |
| Tipo | Petición / Queja / Reclamo / Sugerencia / Denuncia |
| Desde / Hasta | Rango de fechas de radicación |
| Ordenar | Más recientes, más antiguos, por vencimiento, por código |

Haga clic en **"Filtrar"** para aplicar y en **"Limpiar filtros"** para restablecer.

### Tabla de solicitudes

Cada fila muestra: **Código · Tipo · Tipo Persona · Asunto · Solicitante · Fecha · Vencimiento · Estado**

Las filas se colorean según la urgencia de vencimiento:
- 🔴 **Rojo** — vencidas o vencen en menos de 5 días
- 🟡 **Amarillo/Naranja** — vencen entre 6 y 10 días
- 🔵 **Azul** — vencen entre 11 y 15 días

### Acciones por PQRS

| Ícono | Función |
|-------|---------|
| 👁️ Ver | Abre el detalle completo de la solicitud |
| ↩️ Responder | Abre el formulario de respuesta |
| 🕐 Historial | Abre la línea de tiempo de acciones |

---

## 7. Ver Detalle de una PQRS

Haga clic en el ícono 👁️ de cualquier solicitud.

Verá:
- Encabezado con código, tipo, estado y urgencia de vencimiento
- **Información de la solicitud:** asunto, descripción completa y archivo adjunto (con previsualización)
- **Datos del solicitante:** según tipo de persona (Natural, Jurídica o Anónima)
- **Respuesta oficial** (si ya fue respondida)
- **Cambiar Estado** — formulario lateral para actualizar el estado con comentario opcional
- **Historial reciente** — últimas 5 acciones registradas con enlace a ver todo

### Cambiar estado desde el detalle

En el panel lateral "Cambiar Estado":
1. Seleccione el nuevo estado en el desplegable
2. Escriba un comentario opcional sobre el cambio
3. Haga clic en **"Actualizar Estado"**

**Transiciones de estado permitidas:**
```
PENDIENTE → EN_PROCESO → RESUELTO
PENDIENTE → RECHAZADO
EN_PROCESO → RESUELTO
EN_PROCESO → RECHAZADO
```

> Una vez **Resuelto** o **Rechazado**, el estado es definitivo y no puede cambiarse.

---

## 8. Responder una PQRS

Haga clic en el ícono ↩️ de cualquier solicitud, o en el botón **"Responder"** desde el detalle.

1. Redacte la respuesta formal en el campo de texto
2. Seleccione si desea cambiar el estado al responder (opcional)
3. Haga clic en **"Enviar Respuesta"**

Si el solicitante tiene correo registrado y activó las notificaciones, el sistema enviará automáticamente una notificación por correo con la respuesta.

> La respuesta será visible al ciudadano en la sección de **"Consultar Estado"**.

---

## 9. Historial de Acciones

Haga clic en el ícono 🕐 de cualquier solicitud, o en **"Historial completo"** desde el detalle.

Verá una línea de tiempo cronológica (más reciente primero) con cada acción registrada:
- Fecha y hora del evento
- Tipo de acción (VISUALIZACIÓN, CAMBIO_ESTADO, RESPUESTA, etc.)
- Descripción detallada del cambio
- Estado anterior → estado nuevo
- Nombre del administrador que realizó la acción

Al final de la línea siempre aparece el evento de creación de la solicitud.

---

## 10. Centro de Alertas

Acceda desde el acceso rápido o `index.php?ruta=admin/alertas`.

Las PQRS con estado **PENDIENTE** o **EN PROCESO** se agrupan en 4 niveles de urgencia:

| Nivel | Criterio | Color |
|-------|----------|-------|
| **Vencidas** | Fecha de vencimiento ya pasó | 🔴 Rojo oscuro |
| **Crítico** | Vencen en 0–5 días | 🔴 Rojo |
| **Urgente** | Vencen en 6–10 días | 🟡 Naranja |
| **Moderado** | Vencen en 11–15 días | 🟢 Verde |

Desde cada alerta puede ir directamente a **Ver** o **Responder** la solicitud sin pasos adicionales.

---

## 11. Reportes de Cumplimiento

Acceda desde el acceso rápido o `index.php?ruta=admin/reportes`.

### Filtros disponibles
- **Desde / Hasta:** rango de fechas de radicación
- **Tipo:** filtrar por tipo de solicitud específico (o "Todos los tipos")

Haga clic en **"Generar Reporte"** para actualizar los resultados.

### Métricas mostradas
- Total recibidas en el período
- Total resueltas
- Pendientes (incluye en proceso)
- Rechazadas
- Tiempo promedio de respuesta en días
- Porcentaje de cumplimiento legal

### Gráficos
- **Distribución por Tipo** — gráfico de dona (Chart.js)
- **Distribución por Estado** — gráfico de barras
- **Tendencia Mensual** — gráfico de líneas (últimos 6 meses)

### Tabla de cumplimiento de términos
Muestra cuántas PQRS fueron resueltas **dentro** y **fuera** del término legal por tipo de solicitud, con el porcentaje de cumplimiento.

### Exportación
- Botón **"Exportar PDF"** — genera y descarga un reporte en formato PDF (incluye gráficos y tablas)
- Botón **"Exportar Excel"** — genera un archivo `.xls` con todos los datos del período, compatible con Microsoft Excel y LibreOffice Calc

---

## 12. Configuración del Perfil y del Sistema

Acceda desde el botón **"Configuración"** en el header superior o mediante `index.php?ruta=admin/configuracion`.

La página tiene dos pestañas:

### Pestaña "Mi Perfil"

**Datos Personales**
- Nombre completo
- Correo electrónico
- *(El nombre de usuario no puede modificarse)*

**Cambiar Contraseña**
- Ingrese su contraseña actual
- Ingrese la nueva contraseña (mínimo 6 caracteres)
- Confirme la nueva contraseña
- Si deja los campos de contraseña en blanco, la contraseña no se modifica

Haga clic en **"Guardar Perfil"**.

### Pestaña "Sistema"

> Solo administradores con acceso al panel pueden modificar estos parámetros.

**Días de Vencimiento por Tipo**  
Defina cuántos días tiene la entidad para responder cada tipo de solicitud:

| Tipo | Rango válido | Por defecto |
|------|-------------|-------------|
| Petición | 1–30 días | 15 días |
| Queja | 1–30 días | 15 días |
| Reclamo | 1–30 días | 15 días |
| Sugerencia | 1–30 días | 15 días |
| Denuncia | 1–30 días | 15 días |

**Datos Institucionales**
- Nombre de la empresa o entidad
- Correo de notificaciones internas

Haga clic en **"Guardar Configuración"**.

---

## 13. Recuperar Contraseña

Si olvidó su contraseña como administrador:

1. En la página de login, haga clic en **"¿Olvidó su contraseña?"**
2. Ingrese su **correo electrónico registrado** y haga clic en **"Enviar Enlace de Recuperación"**
3. Recibirá un correo con el asunto *"Recuperación de Contraseña - Sistema PQRS"*
4. Abra el correo y haga clic en el botón **"Restablecer Contraseña"**
5. Ingrese su nueva contraseña (mínimo 6 caracteres) y confírmela
6. Haga clic en **"Restablecer Contraseña"**
7. Aparecerá un mensaje de éxito con un enlace directo al login

> ⚠️ El enlace de recuperación **expira en 1 hora**. Si no lo usa a tiempo, deberá solicitar uno nuevo.  
> Por seguridad, si el correo no está registrado, no se mostrará ningún error específico.

---

## 14. Cerrar Sesión

Para salir del panel de forma segura, haga clic en el botón **"Cerrar Sesión"** ubicado en la esquina superior derecha del header.

> La sesión también se cierra automáticamente tras **30 minutos de inactividad**.

---

## 15. Estados de una PQRS

| Estado | Significado | ¿Quién lo asigna? |
|--------|-------------|-------------------|
| **Pendiente** | Recién radicada, sin atender | Sistema (automático) |
| **En Proceso** | El administrador la tomó y está gestionando | Administrador |
| **Resuelto** | Se emitió respuesta formal al ciudadano | Administrador |
| **Rechazado** | No procede; se debe incluir justificación | Administrador |

El ciudadano puede ver el estado actual en cualquier momento desde la sección **"Consultar Estado"** de la página de inicio.

---

## 16. Tiempos Legales de Respuesta

Según la normativa colombiana vigente, los términos estándar configurados en el sistema son:

| Tipo de Solicitud | Término Legal | Norma |
|-------------------|---------------|-------|
| Petición | 15 días hábiles | Ley 1755 de 2015, Art. 13 |
| Queja | 15 días hábiles | Ley 1755 de 2015, Art. 14 |
| Reclamo | 15 días hábiles | Ley 1437 de 2011, Art. 56 |
| Sugerencia | 15 días hábiles | Ley 1755 de 2015, Art. 15 |
| Denuncia | 10 días hábiles | Ley 1474 de 2011, Art. 7 |

> Estos términos pueden ajustarse desde **Configuración › Sistema** dentro del rango permitido de 1 a 30 días.

---

## 17. Preguntas Frecuentes

**¿Necesito crear una cuenta para radicar una PQRS?**  
No. El sistema es completamente público para los ciudadanos. No se requiere registro ni cuenta.

**¿Qué hago si perdí mi código de radicado?**  
Si registró su solicitud con un correo electrónico, puede consultar por correo en **"Consultar Estado"** y el sistema mostrará todas sus solicitudes asociadas a ese correo.

**¿Puedo radicar de forma anónima?**  
Sí. Al seleccionar **"Anónima"** en el formulario, el sistema no le pedirá datos personales. Tenga en cuenta que no recibirá notificaciones; guarde el código de radicado.

**¿Cuándo recibiré respuesta a mi solicitud?**  
Dentro de los términos legales establecidos (ver tabla en sección 16). Puede verificar el estado en cualquier momento con su código de radicado.

**¿Qué formatos admite el archivo adjunto?**  
PDF, Word (.doc, .docx), JPG y PNG. El tamaño máximo es de 5 MB.

**No recibí el correo de confirmación. ¿Qué hago?**  
Revise la carpeta de spam. Si el problema persiste, consulte el estado de su PQRS usando su código de radicado. Si el código aparece, su solicitud fue registrada correctamente.

**¿Puedo modificar una PQRS después de enviarla?**  
No. Una vez radicada, la solicitud no puede modificarse. Si cometió un error importante, puede radicar una nueva solicitud o contactar al administrador.

**¿Puedo ver el archivo que adjunté?**  
Sí. En la página de consulta de estado, si la PQRS tiene un archivo adjunto, aparecerá el botón **"Ver archivo adjunto"** que abre una ventana emergente con el archivo (imágenes y PDF se previsualizan; otros formatos se pueden descargar directamente).

**Como administrador, ¿puedo volver a abrir una PQRS resuelta?**  
No. Una vez que una PQRS llega al estado **Resuelto** o **Rechazado**, el estado es definitivo y no puede cambiarse para garantizar la integridad del historial.

**La sesión del administrador se cerró sola. ¿Por qué?**  
Por seguridad, la sesión expira automáticamente después de **30 minutos de inactividad**. Vuelva a iniciar sesión para continuar.

**¿Cómo exporto un reporte a PDF?**  
En la sección de Reportes, configure los filtros deseados, genere el reporte y haga clic en **"Exportar PDF"**. El archivo se descargará automáticamente en su navegador.
