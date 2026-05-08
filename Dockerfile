FROM php:8.2-fpm

# Instalar extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar Caddy (servidor web simple y confiable)
RUN apt-get update && apt-get install -y debian-keyring debian-archive-keyring apt-transport-https \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list \
    && apt-get update && apt-get install -y caddy \
    && rm -rf /var/lib/apt/lists/*

# Configurar Caddy
RUN echo ':${PORT} {\n\
    root * /var/www/html\n\
    php_fastcgi 127.0.0.1:9000\n\
    file_server\n\
    try_files {path} {path}/ /index.php\n\
}' > /etc/caddy/Caddyfile

# Script de inicio
RUN echo '#!/bin/bash\n\
export PORT=${PORT:-80}\n\
sed -i "s/\\${PORT}/$PORT/g" /etc/caddy/Caddyfile\n\
php-fpm --daemonize\n\
caddy run --config /etc/caddy/Caddyfile' > /start.sh && chmod +x /start.sh

# Copiar archivos
COPY . /var/www/html/

# Puerto
EXPOSE 80

CMD ["/start.sh"]