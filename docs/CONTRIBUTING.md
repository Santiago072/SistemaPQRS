# Guía para Colaboradores — Sistema PQRS

¡Gracias por contribuir al **Sistema de Gestión de PQRS**! Esta guía establece los estándares de desarrollo, convenciones de commits y el flujo para asegurar la máxima calidad del código.

---

## 🛠️ Entorno de Desarrollo Local

1. **Requisitos:**
   - PHP 8.2 o superior con extensiones `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`.
   - Composer 2.x instalado globalmente.
   - MySQL 8.0 o MariaDB 10.11+.

2. **Instalación:**
   ```bash
   git clone https://github.com/Santiago072/SistemaPQRS.git
   cd SistemaPQRS
   composer install
   ```

3. **Base de Datos:**
   - Crear base de datos `sistema_pqrs`.
   - Importar `BD.txt`.
   - Copiar `.env.example` a `.env` y configurar `DB_HOST=localhost`.

---

## 🧪 Pruebas Automatizadas

Antes de realizar cualquier commit o enviar un Pull Request, es obligatorio ejecutar la suite de pruebas unitarias y verificar que todas pasen con 100% de éxito:

```bash
# Ejecución estándar
composer test

# Ejecución detallada (TestDox)
composer test-verbose
```

---

## 📝 Convención de Commits (Conventional Commits)

Utilizamos el estándar en español con prefijos semánticos claros:

- `feat:` Nueva funcionalidad para el usuario.
- `fix:` Corrección de un error o bug.
- `refactor:` Reestructuración de código sin alterar su comportamiento externo.
- `test:` Inclusión o ajuste de pruebas automatizadas.
- `docs:` Modificaciones en documentación o manuales.
- `ci:` Cambios en pipelines de integración continua o GitHub Actions.
- `chore:` Tareas de mantenimiento, dependencias o configuración de `.gitignore`.

*Ejemplo:*
```bash
git commit -m "feat: validar correo electronico estrictamente contra la tabla administrador en recuperacion"
```

---

## 📋 Checklist para Pull Requests

- [ ] Las 37 pruebas unitarias pasan exitosamente (`composer test`).
- [ ] No se agregan archivos temporales, logs ni la carpeta `vendor/` al control de versiones.
- [ ] El código sigue las normas PSR-12 y utiliza Prepared Statements de PDO.
- [ ] La documentación en `README.md` o `CHANGELOG.md` fue actualizada si la funcionalidad lo amerita.
