# Especificación de Requisitos: Sistema de Gestión de PQRS para Empresas de Servicios

> **Contexto:** Empresas de servicios en Neiva · **Plataforma:** Web (PHP 8.2 + MySQL, Patrón MVC Front Controller)

---

## 1. Resumen de la Problemática

Las empresas de servicios públicos y privados en Neiva reciben peticiones, quejas, reclamos y sugerencias (PQRS) por múltiples canales (presencial, telefónico, correo) sin contar con un sistema unificado de gestión. Como consecuencia, las solicitudes se pierden con frecuencia, los tiempos de respuesta exceden los límites legales establecidos y no existe trazabilidad del proceso.

Esta situación expone a las empresas a sanciones legales y al deterioro de su imagen institucional. Se requiere un sistema web que **centralice la recepción, asignación, seguimiento y respuesta de solicitudes ciudadanas**, garantizando el cumplimiento de los tiempos de ley y la trazabilidad completa de cada proceso.

---

## 2. Flujo del Sistema

El sistema opera bajo el siguiente flujo de interacción entre el ciudadano y el administrador:

1. **El usuario crea la solicitud:** El ciudadano ingresa al formulario público y completa los datos requeridos para radicar su PQRS.
2. **Generación de código único:** El sistema genera automáticamente un código radicado con formato `PQRS-AAAA-MM-NNN` (ejemplo: `PQRS-2026-05-001`) que identifica de forma única la solicitud.
3. **Almacenamiento en base de datos:** La solicitud y todos sus datos se guardan en la base de datos centralizada con estado inicial **Pendiente**.
4. **Visualización por el administrador:** El panel de administración muestra la solicitud en la bandeja de entrada para su gestión.
5. **Respuesta del administrador:** El administrador cambia el estado de la solicitud y registra una respuesta formal dirigida al ciudadano.
6. **Consulta del ciudadano:** El ciudadano puede consultar en cualquier momento el estado actualizado de su solicitud usando su código o correo electrónico.

---

## 3. Requisitos Funcionales (RF)

Estos requisitos definen las funciones específicas que el sistema debe ejecutar según la estructura de módulos propuesta:

### 3.1 Página de Inicio

- **RF01 — Página Informativa:** El sistema debe mostrar una página de inicio que explique qué es el sistema PQRS, el proceso de radicación y los tiempos de respuesta legales.
- **RF02 — Botón Nueva Solicitud:** La página debe incluir un botón de acceso directo "Nueva Solicitud" que active el flujo de creación.
- **RF03 — Botón Login Administrador:** La página debe mostrar un botón para acceder al panel de administración.

### 3.2 Términos de Uso

- **RF04 — Modal de Términos Legales:** Al presionar "Nueva Solicitud", el sistema debe mostrar un modal con los términos de uso y marco legal aplicable (Ley 1755 de 2015, Ley 1437 de 2011, etc.).
- **RF05 — Aceptación Obligatoria:** El usuario debe aceptar los términos para continuar. Sin aceptación, no puede acceder al formulario.

### 3.3 Sistema de Formulación (Creación de PQRS)

- **RF06 — Tarjetas de Tipos de Solicitud:** El sistema debe mostrar tarjetas visuales para cada tipo de solicitud: **Petición, Queja, Reclamo, Sugerencia y Denuncia**. Cada tarjeta debe incluir una breve descripción.
- **RF07 — Selección de Tipo de Persona:** En cada tarjeta, el usuario debe poder seleccionar el tipo de solicitante:
  - **Persona Natural:** Requiere nombre completo, documento de identidad, correo, teléfono.
  - **Persona Jurídica:** Requiere razón social, NIT, representante legal, correo corporativo, teléfono.
  - **Anónima:** Solo requiere descripción de la solicitud (sin datos personales).
- **RF08 — Formulario Dinámico:** Según la selección de tipo de persona, el sistema debe mostrar el formulario con los campos correspondientes.
- **RF09 — Campos del Formulario:**
  - *Persona Natural:* Nombre, Tipo documento, Número documento, Correo, Teléfono, Asunto, Descripción, Archivo adjunto (opcional).
  - *Persona Jurídica:* Razón social, NIT, Nombre representante, Correo corporativo, Teléfono, Asunto, Descripción, Archivo adjunto (opcional).
  - *Anónima:* Asunto, Descripción, Archivo adjunto (opcional).
- **RF10 — Generación de Código Único:** Al enviar el formulario, el sistema genera automáticamente un código de radicado con formato `PQRS-AAAA-MM-NNN`. El consecutivo reinicia cada mes.
- **RF11 — Confirmación de Radicación:** El sistema debe mostrar pantalla de confirmación con el código generado y, si aplica, enviar notificación al correo del solicitante.

### 3.4 Consulta de Estado

- **RF12 — Portal de Consulta:** El sistema debe ofrecer una sección donde el ciudadano pueda consultar el estado de su solicitud.
- **RF13 — Búsqueda por Código o Correo:** La consulta se realiza ingresando el código de radicado **O** el correo electrónico registrado.
- **RF14 — Visualización de Estado:** La consulta debe mostrar: estado actual, fecha de radicación, tipo de solicitud, y respuesta del administrador (cuando esté disponible). Si consulta por correo, se muestra el listado de todas sus solicitudes.

### 3.5 Panel de Administrador

- **RF15 — Login de Administrador:** Acceso mediante usuario y contraseña desde el botón en la página de inicio.
- **RF16 — Bandeja de Solicitudes:** El panel debe mostrar todas las PQRS recibidas en formato de lista/tabla con: código, tipo, tipo de persona, asunto, fecha, estado.
- **RF17 — Filtrado de Solicitudes:** Filtros por estado (Pendiente, En proceso, Resuelto, Rechazado), tipo de solicitud, y rango de fechas.
- **RF18 — Gestión de Solicitud:** El administrador puede ver el detalle completo, cambiar estado y redactar respuesta.
- **RF19 — Historial de Acciones:** Registro de todos los cambios realizados en cada solicitud (quién, cuándo, qué acción).
- **RF20 — Reportes de Cumplimiento:** El sistema debe generar reportes filtrables por tipo de solicitud y rango de fechas, con exportación a PDF y Excel, permitiendo a la dirección evaluar el desempeño institucional frente a los términos legales.
- **RF21 — Alertas de Vencimiento:** Visualización cuando una o varias PQRS se aproximen al vencimiento legal (5, 10, 15 días según tipo). Indicadores visuales por colores de urgencia en la bandeja.
- **RF22 — Configuración de Perfil del Administrador:** El sistema debe permitir al administrador autenticado modificar su información personal (nombre completo, correo electrónico, contraseña).
- **RF23 — Recuperación de Contraseña del Administrador:** En el login debe permitir al administrador restablecer su contraseña mediante un enlace de recuperación enviado a su correo electrónico registrado, utilizando un token temporal de un solo uso con expiración de 1 hora.
- **RF24 — Configuración del Sistema:** El sistema debe permitir al administrador modificar desde el panel los parámetros operativos: días de vencimiento por tipo de solicitud, correo de notificaciones y nombre de la empresa.

---

## 4. Requisitos No Funcionales (RNF)

Estos requisitos definen los atributos de calidad, trazabilidad y operación del sistema:

- **RNF01 — Formato de Radicado Consecutivo:** El sistema debe generar automáticamente un número de radicado con formato `PQRS-AAAA-MM-NNN`, garantizando la unicidad e identificación cronológica de cada registro. El consecutivo reinicia en 001 al inicio de cada mes.
- **RNF02 — Persistencia de Información:** Todos los registros de solicitudes, respuestas, estados e historial deben almacenarse en una base de datos centralizada con respaldo que garantice su disponibilidad y consulta posterior.
- **RNF03 — Gestión de Sesión Segura:** El módulo de login del administrador debe implementar gestión de sesión activa (basada en sesiones PHP) para proteger el acceso al panel interno. La sesión expira automáticamente tras 30 minutos de inactividad.
- **RNF04 — Protección contra Inyección SQL:** Todas las consultas a la base de datos deben utilizar sentencias preparadas (`prepared statements` con `mysqli`) para prevenir inyecciones SQL.
- **RNF05 — Protección XSS:** Toda salida a HTML debe utilizar `htmlspecialchars()`. Los archivos adjuntos se renombran con timestamp y solo se guarda el nombre; su contenido nunca se ejecuta.
- **RNF06 — Validación de Datos:** El sistema debe validar la entrada tanto en el cliente (HTML5 + JavaScript en tiempo real) como en el servidor (formato, longitud, tipo), incluyendo documentos, correos, teléfonos y archivos.
- **RNF07 — Gestión de Archivos Adjuntos:** Solo se permiten extensiones `.pdf`, `.doc`, `.docx`, `.jpg`, `.jpeg`, `.png`. Tamaño máximo: 5 MB. Los archivos se renombran con `time()` para evitar colisiones.

---

## 5. Páginas del Sistema (Estructura Web)

El sistema se compone de las siguientes páginas funcionales, todas enrutadas a través de `index.php?ruta=`:

| N° | Página | Ruta MVC | Descripción |
|----|--------|----------|-------------|
| 1 | Inicio | `home/index` | Explicación del sistema + botones de acción |
| 2 | Términos de Uso | (modal en inicio) | Marco legal y aceptación obligatoria |
| 3 | Tipos de PQRS | `pqrs/tipos` | Tarjetas visuales de tipos de solicitud |
| 4 | Formulario PQRS | `pqrs/formulario` | Formulario dinámico adaptable por tipo de persona |
| 5 | Confirmación | `pqrs/confirmacion` | Código radicado generado + envío de correo |
| 6 | Consultar Estado | `pqrs/consulta` | Búsqueda por código o correo electrónico |
| 7 | Login Admin | `admin/login` | Formulario de autenticación del administrador |
| 8 | Dashboard | `admin/dashboard` | KPIs, estadísticas y accesos rápidos |
| 9 | Bandeja PQRS | `admin/pqrs` | Lista filtrable de todas las solicitudes |
| 10 | Detalle PQRS | `admin/pqrs_ver` | Vista completa + cambio de estado |
| 11 | Responder PQRS | `admin/pqrs_responder` | Editor de respuesta formal |
| 12 | Historial | `admin/pqrs_historial` | Timeline de acciones por PQRS |
| 13 | Centro de Alertas | `admin/alertas` | PQRS próximas a vencer |
| 14 | Reportes | `admin/reportes` | Métricas + gráficos + exportación |
| 15 | Configuración | `admin/configuracion` | Perfil del admin + parámetros del sistema |
| 16 | Recuperar Contraseña | `auth/recuperar` | Formulario de recuperación por correo |

---

## 6. Estados de la PQRS

Cada solicitud debe transitar por los siguientes estados claramente definidos:

| Estado | Descripción | Asignado por |
|--------|-------------|--------------|
| **Pendiente** | Estado inicial automático al momento de la radicación. La solicitud aún no ha sido atendida. | Sistema (automático) |
| **En Proceso** | El administrador ha tomado la solicitud y está trabajando en su gestión. Visible al ciudadano en la consulta pública. | Administrador |
| **Resuelto** | La solicitud fue atendida y se emitió una respuesta formal al ciudadano. Estado final positivo. | Administrador |
| **Rechazado** | La solicitud fue revisada pero no procede. El administrador debe incluir una justificación en la respuesta. | Administrador |

> **Transiciones permitidas:**
> ```
> PENDIENTE → EN_PROCESO → RESUELTO
> PENDIENTE → RECHAZADO
> EN_PROCESO → RESUELTO
> EN_PROCESO → RECHAZADO
> ```
> Una vez **Resuelto** o **Rechazado**, el estado no puede modificarse.

---

## 7. Módulos del Sistema — Sprints Scrum

El desarrollo está organizado en 4 sprints de acuerdo con la metodología Scrum:

---

### Sprint 1: Página de Inicio + Términos + Formulario PQRS

#### HU-01 — Página de Inicio
> **Como** ciudadano, **quiero** ver una página de inicio que explique qué es el sistema PQRS, el proceso de radicación y los tiempos legales, con botones para "Nueva Solicitud", "Consultar Estado" e "Iniciar Sesión", **para** entender el sistema y acceder rápidamente a lo que necesito.

**Criterios de Aceptación:**
- ✅ Explica qué es PQRS y sus tipos (P, Q, R, S, D)
- ✅ Muestra el proceso de radicación y tiempos legales
- ✅ Botón "Nueva Solicitud" prominente
- ✅ Botón "Consultar Estado"
- ✅ Botón "Login Admin" discreto (esquina superior)
- ✅ Diseño responsive

#### HU-02 — Términos de Uso
> **Como** ciudadano, **quiero** que al presionar "Nueva Solicitud" aparezca un modal con los términos de uso y marco legal que debo aceptar obligatoriamente, **para** conocer mis derechos y dar mi consentimiento informado antes de continuar.

**Criterios de Aceptación:**
- ✅ Muestra leyes aplicables (Ley 1755 de 2015, Ley 1437 de 2011, etc.)
- ✅ Casilla "Acepto términos" obligatoria
- ✅ Sin aceptación no puede acceder al formulario
- ✅ Botón de cerrar modal disponible

#### HU-03 — Tipos de Solicitud
> **Como** ciudadano, **quiero** ver tarjetas visuales para cada tipo de solicitud (Petición, Queja, Reclamo, Sugerencia, Denuncia), **para** escoger el tipo correcto y realizar la radicación.

**Criterios de Aceptación:**
- ✅ 5 tarjetas con ícono, nombre y descripción
- ✅ Descripción breve de implicaciones de cada tipo

#### HU-04 — Formulario Adaptable
> **Como** ciudadano, **quiero** seleccionar si soy Persona Natural, Jurídica o Anónima y que el formulario se adapte dinámicamente con los campos correspondientes, **para** proporcionar solo la información requerida.

**Criterios de Aceptación:**
- ✅ Natural: Nombre, Tipo/N° documento, Correo, Teléfono, Asunto, Descripción, Adjunto opcional
- ✅ Jurídica: Razón social, NIT, Representante, Correo corporativo, Teléfono, Asunto, Descripción, Adjunto opcional
- ✅ Anónima: Asunto, Descripción, Adjunto opcional
- ✅ Validaciones en tiempo real
- ✅ Marcado de campos obligatorios

#### HU-05 — Generación de Código
> **Como** ciudadano, **quiero** que al enviar el formulario se genere un código único `PQRS-AAAA-MM-NNN` y vea una pantalla de confirmación, **para** tener mi referencia de radicación y recibir notificación por correo si aplica.

**Criterios de Aceptación:**
- ✅ Formato: `PQRS-Año-Mes-Consecutivo` (3 dígitos, con ceros a la izquierda)
- ✅ Consecutivo reinicia en 001 cada mes
- ✅ Pantalla de confirmación con código visible y botón para copiar
- ✅ Envío de notificación al correo (si el solicitante proporcionó correo y aceptó notificaciones)

---

### Sprint 2: Panel de Administrador + Login + Bandeja

#### HU-06 — Login Admin
> **Como** administrador, **quiero** acceder al panel mediante usuario y contraseña desde el botón en la página de inicio, con sesión activa durante la navegación, **para** gestionar solicitudes de forma segura sin interrupciones.

**Criterios de Aceptación:**
- ✅ Formulario de login con validación
- ✅ Sesión activa con expiración por inactividad (30 minutos)
- ✅ Opción de cerrar sesión manual

#### HU-07 — Bandeja de Solicitudes PQRS
> **Como** administrador, **quiero** ver una bandeja con todas las PQRS recibidas en formato tabla y poder filtrarlas por estado, tipo y fechas, **para** gestionarlas eficientemente.

**Criterios de Aceptación:**
- ✅ Tabla con: Código, Tipo, Tipo persona, Asunto, Fecha, Estado
- ✅ Filtros por estado (Pendiente, En proceso, Resuelto, Rechazado)
- ✅ Filtro por tipo de solicitud
- ✅ Filtro por rango de fechas
- ✅ Ordenamiento por fecha (más recientes primero)
- ✅ Indicadores de color por urgencia de vencimiento

#### HU-08 — Detalle y Respuesta a Solicitudes
> **Como** administrador, **quiero** ver el detalle completo de una solicitud, cambiar su estado y redactar una respuesta formal, **para** gestionar el caso y dar trámite oficial al ciudadano.

**Criterios de Aceptación:**
- ✅ Muestra todos los datos del solicitante según tipo de persona (Natural, Jurídica, Anónima)
- ✅ Cambio de estado: `Pendiente → En Proceso → Resuelto/Rechazado`
- ✅ Editor para redactar respuesta
- ✅ Respuesta visible en consulta pública del ciudadano
- ✅ Registro automático de fecha/hora/usuario en historial

#### HU-09 — Recuperación de Contraseña del Administrador
> **Como** administrador que olvidó su contraseña, **quiero** poder restablecerla mediante un enlace enviado a mi correo electrónico, **para** recuperar el acceso sin depender de otro administrador.

**Criterios de Aceptación:**
- ✅ La página de login muestra un enlace "¿Olvidó su contraseña?"
- ✅ El formulario de recuperación solicita el correo del administrador
- ✅ Se genera un token de 64 caracteres con expiración de 1 hora
- ✅ Se envía correo con enlace al formulario de restablecimiento
- ✅ La nueva contraseña debe tener mínimo 6 caracteres
- ✅ Al restablecer exitosamente, el token se elimina de la BD
- ✅ Si el token es inválido o expirado, se muestra mensaje de error

#### HU-10 — Configuración del Sistema
> **Como** administrador, **quiero** ajustar los parámetros del sistema (días de vencimiento por tipo y datos institucionales) desde el panel, **para** no depender de acceso directo a la base de datos.

**Criterios de Aceptación:**
- ✅ Acceso desde el header del panel de administración
- ✅ Formulario con los días de vencimiento para cada tipo (Petición, Queja, Reclamo, Sugerencia, Denuncia)
- ✅ Campo para correo de notificaciones y nombre de la empresa
- ✅ Validación: días entre 1 y 30, correo válido
- ✅ Mensaje de éxito o error al guardar

---

### Sprint 3: Consulta de Estado + Alertas de Vencimiento

#### HU-11 — Consultar Estado de Solicitud
> **Como** ciudadano, **quiero** consultar el estado de mi solicitud ingresando mi código de radicado o mi correo electrónico, **para** hacer seguimiento sin crear cuenta y ver la respuesta del administrador cuando esté disponible.

**Criterios de Aceptación:**
- ✅ Campo para código `PQRS-AAAA-MM-NNN`
- ✅ Campo para correo electrónico
- ✅ Muestra: estado actual con barra de progreso, fecha de radicación, tipo, descripción, archivo adjunto
- ✅ Muestra respuesta del administrador cuando esté disponible
- ✅ Si consulta por correo: listado de todas las solicitudes asociadas

#### HU-12 — Alertas de Vencimiento
> **Como** administrador, **quiero** visualizar cuando una o varias PQRS se acerquen al vencimiento legal, **para** atenderlas a tiempo y evitar sanciones.

**Criterios de Aceptación:**
- ✅ Alertas a 5, 10 y 15 días según tipo de solicitud
- ✅ Indicadores visuales en la bandeja (colores por urgencia: 🔴 0–5 días, 🟡 6–10 días, 🔵 11–15 días)
- ✅ Centro de Alertas con PQRS agrupadas por nivel de urgencia
- ✅ Indicador en el header del panel cuando hay PQRS próximas a vencer

---

### Sprint 4: Historial de Acciones + Reportes de Cumplimiento

#### HU-13 — Historial de Acciones
> **Como** administrador, **quiero** ver el historial completo de acciones realizadas en cada solicitud, **para** tener trazabilidad total del proceso.

**Criterios de Aceptación:**
- ✅ Lista cronológica de cambios (más reciente primero)
- ✅ Muestra: fecha, hora, administrador, acción realizada, detalle
- ✅ Incluye cambios de estado y respuestas enviadas
- ✅ Muestra el estado anterior y el estado nuevo en cada cambio

#### HU-14 — Generación de Reportes
> **Como** administrador, **quiero** generar reportes de cumplimiento filtrables por tipo de solicitud y rango de fechas, **para** evaluar el desempeño institucional frente a los términos legales.

**Criterios de Aceptación:**
- ✅ Filtros por tipo de solicitud y rango de fechas
- ✅ Métricas: total recibidas, resueltas, pendientes (en proceso), rechazadas, tiempo promedio de respuesta
- ✅ Visualización en gráficos (dona por tipo, barras por estado, líneas tendencia mensual)
- ✅ Tabla de cumplimiento de términos legales (resueltas dentro/fuera de plazo)
- ✅ Exportación a **PDF** (DomPDF) y **Excel** (tabla HTML con cabeceras MIME)

---

## 8. Beneficios Esperados

| Beneficio | Descripción |
|-----------|-------------|
| **Cumplimiento Legal** | Garantizar la respuesta oportuna dentro de los términos legales establecidos, evitando sanciones a la empresa |
| **Centralización y Trazabilidad** | Pasar de un manejo disperso por múltiples canales a un sistema unificado con trazabilidad completa desde la radicación hasta la respuesta |
| **Transparencia para el Ciudadano** | Ofrecer un canal formal y accesible donde el ciudadano pueda hacer seguimiento real de su solicitud sin depender de llamadas o visitas presenciales |
| **Mejora de la Imagen Institucional** | Fortalecer la confianza ciudadana mediante un proceso de atención organizado, documentado y eficiente |
