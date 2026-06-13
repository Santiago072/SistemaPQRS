# Especificación de Requisitos: Sistema de Gestión de PQRS para Empresas de Servicios

> **Empresa:** Empresas de Servicios Públicos y Privados en Neiva · **Plataforma:** Web

---

## 1. Resumen de la Problemática
Las empresas de servicios públicos y privados en Neiva reciben peticiones, quejas, reclamos y sugerencias (PQRS) por múltiples canales (presencial, telefónico, correo) sin contar con un sistema unificado de gestión. Como consecuencia, las solicitudes se pierden con frecuencia, los tiempos de respuesta exceden los límites legales establecidos y no existe trazabilidad del proceso. Esta situación expone a las empresas a sanciones legales y al deterioro de su imagen institucional. Se requiere un sistema web que centralice la recepción, asignación, seguimiento y respuesta de solicitudes ciudadanas, garantizando el cumplimiento de los tiempos de ley y la trazabilidad completa de cada proceso.

---

## 2. Flujo del Sistema
El sistema opera bajo el siguiente flujo de interacción entre el ciudadano y el administrador:

1. **El Usuario crea la solicitud**: El ciudadano ingresa al formulario público y completa los datos requeridos para radicar su PQRS.
2. **Generación de código único**: El sistema genera automáticamente un código radicado con formato `PQRS-AAAA-NNN` (ejemplo: `PQRS-2026-05-001`) que identifica de forma única la solicitud.
3. **Almacenamiento en base de datos**: La solicitud y todos sus datos se guardan en la base de datos centralizada con estado inicial *Pendiente*.
4. **Visualización por el administrador**: El panel de administración muestra la solicitud en la bandeja de entrada para su gestión.
5. **Respuesta del administrador**: El administrador cambia el estado de la solicitud y registra una respuesta formal dirigida al ciudadano.
6. **Consulta del ciudadano**: El ciudadano puede consultar en cualquier momento el estado actualizado de su solicitud usando su código o correo electrónico.

---

## 3. Requisitos Funcionales (RF)
Estos requisitos definen las funciones específicas que el sistema debe ejecutar según la estructura de módulos propuesta:

### 3.1 Página de Inicio
- **RF01 - Pagina Informativa**: El sistema debe mostrar una página de inicio que explique qué es el sistema PQRS, el proceso de radicación y los tiempos de respuesta legales.
- **RF02 - Botón Nueva Solicitud**: La página debe incluir un botón de acceso directo "Nueva Solicitud" que active el flujo de creación.
- **RF03 - Botón Login Administrador**: La página debe mostrar un botón para acceder al panel de administración.

### 3.2 Términos de Uso
- **RF04 - Modal de Términos Legales**: Al presionar "Nueva Solicitud", el sistema debe mostrar un modal con los términos de uso y marco legal aplicable (Ley 1755 de 2015, Ley 1437 de 2011, etc.).
- **RF05 - Aceptación Obligatoria**: El usuario debe aceptar los términos para continuar. Sin aceptación, no puede acceder al formulario.

### 3.3 Sistema de Formulación (Creación de PQRS)
- **RF06 - Tarjetas de Tipos de Solicitud**: El sistema debe mostrar tarjetas visuales para cada tipo de solicitud: Petición, Queja, Reclamo, Sugerencia y Denuncia. Cada tarjeta debe incluir una breve descripción.
- **RF07 - Selección de Tipo de Persona**: En cada tarjeta, el usuario debe poder seleccionar el tipo de solicitante:
  - *Persona Natural*: Requiere nombre completo, documento de identidad, correo, teléfono.
  - *Persona Jurídica*: Requiere razón social, NIT, representante legal, correo corporativo, teléfono.
  - *Anónima*: Solo requiere descripción de la solicitud (sin datos personales).
- **RF08 - Formulario Dinámico**: Según la selección de tipo de persona, el sistema debe mostrar el formulario con los campos correspondientes.
- **RF09 - Campos del Formulario**:
  - *Persona Natural*: Nombre, Tipo documento, Numero documento, Correo, Teléfono, Asunto, Descripción, Archivo adjunto (opcional).
  - *Persona Jurídica*: Razón social, NIT, Nombre representante, Correo corporativo, Teléfono, Asunto, Descripción, Archivo adjunto (opcional).
  - *Anónima*: Asunto, Descripción, Archivo adjunto (opcional).
- **RF10 - Generación de Código Único**: Al enviar el formulario, el sistema genera automáticamente un código de radicado con formato `PQRS-AAAA-MMMM-NNN`.
- **RF11 - Confirmación de Radicación**: El sistema debe mostrar pantalla de confirmación con el código generado y, si aplica, enviar notificación al correo del solicitante.

### 3.4 Consulta de Estado
- **RF12 - Portal de Consulta**: El sistema debe ofrecer una sección donde el ciudadano pueda consultar el estado de su solicitud.
- **RF13 - Búsqueda por Código o Correo**: La consulta se realiza ingresando el código de radicado O el correo electrónico registrado.
- **RF14 - Visualización de Estado**: La consulta debe mostrar: estado actual, fecha de radicación, tipo de solicitud, y respuesta del administrador (cuando esté disponible).

### 3.5 Panel de Administrador
- **RF15 - Login de Administrador**: Acceso mediante usuario y contraseña desde el botón en la página de inicio.
- **RF16 - Bandeja de Solicitudes**: El panel debe mostrar todas las PQRS recibidas en formato de lista/tabla con: código, tipo, tipo de persona, asunto, fecha, estado.
- **RF17 - Filtrado de Solicitudes**: Filtros por estado (Pendiente, En proceso, Resuelto, Rechazado), tipo de solicitud, y rango de fechas.
- **RF18 - Gestión de Solicitud**: El administrador puede ver el detalle completo, cambiar estado y redactar respuesta.
- **RF19 - Historial de Acciones**: Registro de todos los cambios realizados en cada solicitud.
- **RF20 - Reportes de Cumplimiento**: El sistema debe generar reportes filtrables por tipo de solicitud y rango de fechas, permitiendo a la dirección evaluar el desempeño institucional frente a los términos legales.
- **RF21 - Alertas de Vencimiento**: Visualización cuando una o varias PQRS se aproxime al vencimiento legal (5, 10, 15 días según tipo).
- **RF22 - Configuración de Perfil del Administrador**: El sistema debe permitir al administrador autenticado modificar su información personal (nombre completo, correo electrónico, contraseña).
- **RF23 - Recuperación de Contraseña del Administrador**: En el login debe permitir al administrador restablecer su contraseña mediante un enlace de recuperación enviado a su correo electrónico registrado, utilizando un token temporal de un solo uso.
- **RF24 - Configuración del Sistema**: El sistema debe permitir al administrador modificar desde el panel los parámetros operativos: días de vencimiento por tipo de solicitud (Petición, Queja, Reclamo, Sugerencia, Denuncia), correo de notificaciones y nombre de la empresa.

---

## 4. Requisitos No Funcionales (RNF)
Estos requisitos definen los atributos de calidad, trazabilidad y operación del sistema:

- **RNF01 - Formato de Radicado Consecutivo**: El sistema debe generar automáticamente un número de radicado con formato `PQRS-AAAA-MMMM-NNN`, garantizando la unicidad e identificación cronológica de cada registro (ejemplo: `PQRS-2026-05-001`).
- **RNF02 - Persistencia de Información**: Todos los registros de solicitudes, respuestas, estados e historial deben almacenarse en una base de datos centralizada con respaldo que garantice su disponibilidad y consulta posterior.
- **RNF03 - Gestión de Sesión Segura**: El módulo de login del administrador debe implementar gestión de sesión activa (session-based o token-based) para proteger el acceso al panel interno y también la implementación de recuperación de contraseña.

---

## 5. Páginas del Sistema (Estructura Web)
El sistema se compone de las siguientes páginas funcionales:

1. **Inicio**: Explicación del sistema + Botón "Nueva Solicitud" + Botón "Login Admin"
2. **Modal Términos de Uso**: Marco legal y aceptación obligatoria
3. **Formulación PQRS**: Tarjetas de tipos + Selección tipo persona + Formulario dinámico
4. **Confirmación**: Código radicado generado
5. **Consultar Estado**: Búsqueda por código o correo
6. **Login Administrador**: Formulario de acceso
7. **Panel Administrador**: Bandeja de solicitudes, gestión, respuestas, alertas, configuración del perfil

---

## 6. Estados de la PQRS
Cada solicitud debe transitar por los siguientes estados claramente definidos:

- **Pendiente**: Estado inicial asignado automáticamente al momento de la radicación. Indica que la solicitud aún no ha sido atendida por ningún funcionario.
- **En proceso**: El administrador ha tomado la solicitud y está trabajando en su gestión o investigación. El ciudadano puede ver este estado en la consulta pública.
- **Resuelto**: La solicitud fue atendida y se emitió una respuesta formal al ciudadano. Es el estado final positivo del proceso.
- **Rechazado**: La solicitud fue revisada pero no procede por motivos establecidos. El administrador debe incluir una justificación en la respuesta.

---

## 7. Módulos del Sistema (Sprints Scrum)
De acuerdo con los sprints definidos en la metodología Scrum, el desarrollo se organiza en los siguientes módulos:

### Sprint 1: Página de inicio + Modal términos + Sistema de tarjetas y formularios dinámicos

- **HU-01 Página Inicio**: Como ciudadano, quiero ver una página de inicio que explique qué es el sistema PQRS, el proceso de radicación y los tiempos de respuesta legales, con botones para "Nueva Solicitud", “Consultar Estado” e "Iniciar Sesión" como administrador, para entender el sistema y acceder rápidamente a lo que necesito.
  - *Criterios de Aceptación*:
    - Explica qué es PQRS y sus tipos (P, Q, R, S, D)
    - Muestra proceso de radicación y tiempos legales
    - Botón "Nueva Solicitud" prominente
    - Botón “Consultar Estado”
    - Botón "Login Admin" discreto (ej: esquina superior)
    - Diseño responsive

- **HU-02 Términos de Uso**: Como ciudadano, quiero que al presionar "Nueva Solicitud" aparezca un modal con los términos de uso y marco legal aplicable que debo aceptar obligatoriamente, para conocer mis derechos y dar mi consentimiento informado antes de continuar.
  - *Criterios de Aceptación*:
    - Muestra leyes aplicables (Ley 1755 de 2015, Ley 1437 de 2011, etc.)
    - Casilla "Acepto términos" obligatoria
    - Sin aceptación no puede acceder al formulario
    - Botón de cerrar modal disponible

- **HU-03 Tipos de Solicitud**: Como ciudadano, quiero ver tarjetas visuales para cada tipo de solicitud (Petición, Queja, Reclamo, Sugerencia, Denuncia), para escoger el tipo de solicitud correcta y realizar la radicación.
  - *Criterios de Aceptación*:
    - 5 tarjetas con icono, nombre y descripción
    - Descripción breve de implicaciones de cada tipo

- **HU-04 Formulario Adaptable**: Como ciudadano, quiero seleccionar si soy Persona Natural, Jurídica o Anónima y que el formulario se adapte dinámicamente según mi tipo de persona con los campos correspondientes, para proporcionar solo la información requerida.
  - *Criterios de Aceptación*:
    - Natural: Nombre, Tipo/N° documento, Correo, Teléfono, Asunto, Descripción, Adjunto opcional
    - Jurídica: Razón social, NIT, Representante, Correo corporativo, Teléfono, Asunto, Descripción, Adjunto opcional
    - Anónima: Asunto, Descripción, Adjunto opcional
    - Validaciones en tiempo real
    - Marcado de campos obligatorios

- **HU-05 Generación de Código**: Como ciudadano, quiero que al enviar el formulario se genere un código único `PQRS-AAAA-MMMM-NNN` y vea una pantalla de confirmación con el código, para tener mi referencia de radicación y recibir notificación por correo si aplica.
  - *Criterios de Aceptación*:
    - Formato: `PQRS-Año-Mes-Consecutivo` (3 dígitos)
    - Consecutivo reinicia cada mes
    - Pantalla de confirmación con código visible
    - Envío de notificación al correo (si aplica)

### Sprint 2: Panel de administrador + Login + Bandeja de solicitudes

- **HU-06 Login Admin**: Como administrador, quiero acceder al panel mediante usuario y contraseña desde el botón en la página de inicio, con sesión activa durante la navegación, para gestionar solicitudes de forma segura sin interrupciones.
  - *Criterios de Aceptación*:
    - Formulario de login con validación
    - Sesión activa con expiración por inactividad
    - Opción de cerrar sesión manual

- **HU-07 Bandeja Solicitudes PQRS**: Como administrador, quiero ver una bandeja con todas las PQRS recibidas en formato lista/tabla y poder filtrarlas por estado, tipo y fechas, para gestionarlas eficientemente.
  - *Criterios de Aceptación*:
    - Tabla con: Código, Tipo, Tipo persona, Asunto, Fecha, Estado
    - Filtros por estado (Pendiente, En proceso, Resuelto, Rechazado)
    - Filtro por tipo de solicitud
    - Filtro por rango de fechas
    - Ordenamiento por fecha (más recientes primero)

- **HU-08 Detalle y Respuestas a Solicitudes**: Como administrador, quiero ver el detalle completo de una solicitud, cambiar su estado y redactar una respuesta formal, para gestionar el caso y dar trámite oficial al ciudadano.
  - *Criterios de Aceptación*:
    - Muestra todos los datos del solicitante según tipo de persona
    - Cambio de estado: Pendiente → En proceso → Resuelto/Rechazado
    - Editor para redactar respuesta
    - Respuesta visible en consulta pública
    - Registro automático de fecha/hora/usuario

- **HU-09 Recuperación de Contraseña del Administrador**: Como administrador que olvidó su contraseña, quiero poder restablecerla mediante un enlace enviado a mi correo electrónico, para recuperar el acceso al sistema sin depender de otro administrador.
  - *Criterios de Aceptación*:
    - La página de login muestra un enlace "¿Olvidó su contraseña?" que lleva al formulario de recuperación.
    - El formulario de recuperación solicita el correo electrónico del administrador.
    - Al enviar el formulario, si el correo existe y la cuenta está activa, se genera un token de 64 caracteres con expiración de 1h y se envía un correo con el enlace de restablecimiento.
    - El correo de recuperación contiene un botón "Restablecer Contraseña" con enlace directo al formulario de restablecimiento.
    - El formulario de restablecimiento permite ingresar y confirmar la nueva contraseña.
    - La nueva contraseña debe tener mínimo 6 caracteres.
    - Al restablecer exitosamente, el token se elimina de la BD y se muestra mensaje de éxito con enlace al login.
    - Si el token es inválido o expirado, se muestra un mensaje de error indicando que solicite uno nuevo.

- **HU-10 Configuración del Sistema**: Como administrador, quiero poder ajustar los parámetros del sistema (días de vencimiento por tipo y datos institucionales) desde el panel, para no depender de acceso directo a la base de datos.
  - *Criterios de Aceptación*:
    - Acceso desde el panel de acceso rápido
    - Formulario con los días de vencimiento para cada tipo (Petición, Queja, Reclamo, Sugerencia, Denuncia)
    - Campo para correo de notificaciones y nombre de la empresa
    - Validación: días entre 1 y 30, correo válido
    - Mensaje de éxito o error al guardar

### Sprint 3: Consulta de estado + Alertas de vencimiento

- **HU-11 Consultar Estado Solicitud**: Como ciudadano, quiero consultar el estado de mi solicitud ingresando mi código de radicado o mi correo electrónico, para hacer seguimiento sin crear cuenta y ver la respuesta del administrador cuando esté disponible.
  - *Criterios de Aceptación*:
    - Campo para código `PQRS-AAAA-MMMM-NNN`
    - Campo para correo electrónico
    - Muestra: estado actual, fecha de radicación, tipo de solicitud, respuesta del admin (cuando esté disponible)
    - Si consulta por correo: listado de todas sus solicitudes

- **HU-12 Alertas de Vencimiento**: Como administrador, quiero visualizar cuando una o varias PQRS se acerque al vencimiento legal, para atenderla a tiempo y evitar sanciones.
  - *Criterios de Aceptación*:
    - Alertas a 5, 10 y 15 días según tipo de solicitud
    - Indicadores visuales en la bandeja (colores por urgencia)
    - Notificación visual en el panel

### Sprint 4: Historial de acciones + Reportes de cumplimiento

- **HU-13 Historial de Acciones**: Como administrador, quiero ver el historial completo de acciones realizadas en cada solicitud, para tener trazabilidad total del proceso.
  - *Criterios de Aceptación*:
    - Lista cronológica de cambios
    - Muestra: fecha, hora, usuario, acción realizada
    - Incluye cambios de estado y respuestas enviadas

- **HU-14 Generación de Reportes**: Como administrador, quiero generar reportes de cumplimiento filtrables por tipo de solicitud y rango de fechas, para evaluar el desempeño institucional frente a los términos legales.
  - *Criterios de Aceptación*:
    - Filtros tipo de solicitud, rango de fechas
    - Métricas: total recibidas, resueltas, pendientes, tiempo promedio
    - Visualización en gráficos/tabla

---

## 8. Beneficios Esperados
- **Cumplimiento Legal**: Garantizar la respuesta oportuna dentro de los términos legales establecidos, evitando sanciones a la empresa.
- **Centralización y Trazabilidad**: Pasar de un manejo disperso por múltiples canales a un sistema unificado con trazabilidad completa desde la radicación hasta la respuesta.
- **Transparencia para el Ciudadano**: Ofrecer un canal formal y accesible donde el ciudadano pueda hacer seguimiento real de su solicitud sin depender de llamadas o visitas presenciales.
- **Mejora de la Imagen Institucional**: Fortalecer la confianza ciudadana en la empresa mediante un proceso de atención organizado, documentado y eficiente.
