# Manual de Despliegue en VPS — Sistema PQRS

Este manual describe el procedimiento paso a paso para poner en producción y mantener actualizado el **Sistema de Gestión de PQRS** en un Servidor Privado Virtual (VPS) con Linux, Docker Compose y Nginx Proxy Manager.

---

## 📋 Requisitos Previos

- Servidor VPS con **Ubuntu 22.04 / 24.04 LTS** o Debian 12.
- **Docker** y **Docker Compose** (Plugin v2) instalados.
- **Git** configurado en el servidor.
- Un nombre de dominio o subdominio apuntando a la IP del VPS (ej. `pqrs.slscode.online`).

---

## 🚀 Paso 1: Clonar el Repositorio

Conéctate vía SSH a tu servidor y descarga la última versión del repositorio:

```bash
cd /var/www  # o el directorio donde gestionas tus aplicaciones
git clone https://github.com/Santiago072/SistemaPQRS.git
cd SistemaPQRS
```

---

## ⚙️ Paso 2: Configurar las Variables de Entorno

Copia la plantilla `.env.example` para crear tu archivo seguro `.env` (este archivo no se versiona en Git):

```bash
cp .env.example .env
nano .env
```

Configura tus credenciales:
```ini
# Configuración del entorno
APP_ENV=production
APP_BASE=/

# Base de datos
DB_HOST=db
DB_NAME=sistema_pqrs
DB_USER=pqrs_user
DB_PASSWORD=TuPasswordSeguro123!

# Servidor de correo (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USER=notificaciones@empresa.com
SMTP_PASSWORD=tu_app_password_gmail
FROM_EMAIL=notificaciones@empresa.com
FROM_NAME="Sistema PQRS Institucional"
```

---

## 🐳 Paso 3: Despliegue Automatizado con `deploy.sh`

El proyecto cuenta con un script de automatización que sincroniza el repositorio, ajusta permisos y compila los contenedores:

```bash
chmod +x deploy.sh
./deploy.sh
```

El script ejecuta automáticamente:
1. `git pull origin master` para descargar los últimos commits.
2. Comprobación y creación de directorios requeridos (`uploads/`, `logs/`).
3. Inicialización o actualización de contenedores con `docker compose up -d --build`.

---

## 🌐 Paso 4: Configurar Proxy Inverso (SSL / HTTPS)

Si utilizas **Nginx Proxy Manager** o Nginx nativo:
- **Forward Hostname / IP:** `sistemapqrs_web` (o la IP local del host).
- **Forward Port:** `8892` (o el puerto configurado en `docker-compose.yml`).
- **Websockets Support:** Activado.
- **SSL Certificate:** Solicitar certificado Let's Encrypt con *Force SSL* y *HTTP/2 Support*.

---

## 🔄 Actualización Continua del Sistema

Cuando realices mejoras en local y hagas `git push`:

1. Ingresa a la terminal de tu VPS.
2. Ejecuta:
   ```bash
   cd SistemaPQRS
   bash deploy.sh
   ```
3. El sistema aplicará los cambios sin caída del servicio y preservando la base de datos.
