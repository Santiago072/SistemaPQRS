FROM php:8.2-fpm

# Instalar extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar dependencias y Caddy
RUN apt-get update && apt-get install -y \
    debian-keyring \
    debian-archive-keyring \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    gettext-base \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list \
    && apt-get update && apt-get install -y caddy \
    && rm -rf /var/lib/apt/lists/*

# Script de inicio (genera Caddyfile dinámicamente)
RUN echo '#!/bin/bash\n\
PORT=${PORT:-80}\n\
echo "Iniciando en puerto: $PORT"\n\
\n\
# Generar Caddyfile con el puerto real\n\
cat > /etc/caddy/Caddyfile << EOF\n\
:${PORT} {\n\
    root * /var/www/html\n\
    php_fastcgi 127.0.0.1:9000\n\
    file_server\n\
    try_files {path} {path}/ /index.php\n\
}\n\
EOF\n\
\n\
# Iniciar PHP-FPM en background\n\
php-fpm --daemonize\n\
\n\
# Iniciar Caddy\n\
caddy run --config /etc/caddy/Caddyfile --adapter caddyfile' > /start.sh && chmod +x /start.sh

# Copiar archivos del proyecto
COPY . /var/www/html/

# Puerto dinámico (Railway lo sobreescribe)
EXPOSE 80

CMD ["/start.sh"]