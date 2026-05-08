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
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list \
    && apt-get update && apt-get install -y caddy \
    && rm -rf /var/lib/apt/lists/*

# Copiar Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Script de inicio
RUN echo '#!/bin/bash\n\
export PORT=${PORT:-80}\n\
echo "Iniciando en puerto: $PORT"\n\
sed -i "s/\\\${PORT}/$PORT/g" /etc/caddy/Caddyfile\n\
php-fpm --daemonize\n\
caddy run --config /etc/caddy/Caddyfile --adapter caddyfile' > /start.sh && chmod +x /start.sh

# Copiar archivos del proyecto
COPY . /var/www/html/

EXPOSE 80

CMD ["/start.sh"]