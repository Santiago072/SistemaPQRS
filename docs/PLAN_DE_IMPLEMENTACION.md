# Plan de Implementación — Sistema PQRS (v1.4.0)

Este documento describe la planificación técnica, fases de evolución, decisiones de arquitectura y el cronograma de madurez del **Sistema de Gestión de PQRS** bajo el marco legal de Colombia (Ley 1755 de 2015).

---

## 🎯 Objetivos de Ingeniería

1. **Atención Ciudadana Transparente:** Permitir radicación pública (Natural/Jurídica) o Anónima con generación de radicados únicos seriales (`PQRS-AAAA-MM-NNN`) y consulta en tiempo real.
2. **Backoffice de Alta Eficiencia:** Dotar a los administradores de un dashboard de métricas en tiempo real, trazabilidad de historial y respuestas formales con plantillas rápidas.
3. **Calidad y Mantenibilidad:** Arquitectura MVC desacoplada con IoC Container (Reflection API), suite unitaria de 37 tests (PHPUnit 10.5) y CI/CD con GitHub Actions.
4. **Diseño Modular y Adaptativo:** Separación de estilos en 7 módulos CSS independientes y responsivos sin dependencias externas pesadas.

---

## 🏗️ Fases de Implementación

```
┌─────────────────┐     ┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  Fase 1: MVC    │ ──► │  Fase 2: Docker  │ ──► │  Fase 3: Quality │ ──► │  Fase 4: Deploy  │
│  Lógica & BD    │     │  Env & Caddy/Nginx│    │  PHPUnit & CI/CD │     │  VPS Producción  │
└─────────────────┘     └──────────────────┘     └──────────────────┘     └──────────────────┘
```

### Fase 1: Arquitectura y Modelado de Datos (Completada)
- Implementación del patrón MVC nativo y Front Controller (`index.php`).
- Creación del `Container.php` con Reflection API para Inyección de Dependencias.
- Base de datos MySQL/MariaDB con PDO y consultas preparadas contra SQL Injection.
- Vistas responsivas para el portal ciudadano y panel administrativo.

### Fase 2: Dockerización y Resiliencia (Completada)
- Orquestación con `docker-compose.yml` utilizando PHP 8.2 FPM, Caddy/Nginx y MariaDB 10.11.
- Manejo agnóstico de variables de entorno con `EnvLoader.php` para XAMPP y `.env` para producción.
- Automatización de despliegue mediante `deploy.sh` con limpieza de caché y auto-reinicio.

### Fase 3: Pruebas Automatizadas y Calidad de Código (v1.4.0)
- Creación de 37 pruebas unitarias (`tests/Unit/`) con 86 aserciones cubriendo Container, Auth, Modelos y SMTP.
- Configuración de pipeline de Integración Continua con **GitHub Actions** en PHP 8.2.
- Refactorización modular de la hoja de estilos `estilos.css` en 7 submódulos bajo `public/css/modules/`.
- Incorporación de la Licencia MIT y validación estricta de correos contra la tabla `administrador`.

### Fase 4: Operación Continua y Despliegue en VPS (Activa)
- Despliegue automatizado en VPS con proxy inverso seguro y SSL/TLS.
- Sincronización continua de código mediante `git push origin master` y `bash deploy.sh`.

---

## 📊 Matriz de Trazabilidad y Verificación

| Componente | Requisito Legal / Técnico | Mecanismo de Verificación |
|------------|---------------------------|----------------------------|
| Radicado Único | Ley 1755 de 2015 | `PqrsModelTest::codigo_radicado_tiene_formato_correcto` |
| Términos de Respuesta | 15 días hábiles promedio | `PqrsModelTest::fecha_vencimiento_se_calcula_correctamente` |
| Seguridad en Login | Cifrado Bcrypt + CSRF | `AuthControllerTest::password_verify_acepta_hash_bcrypt_valido` |
| Recuperación de Clave | Tokens criptográficos de 1 hora | `AuthControllerTest::recuperar_consulta_correo_en_tabla_administrador` |
| Inyección de Dependencias | Principio SOLID (DIP) | `ContainerTest::resuelve_clase_con_dependencia_inyectada` |
