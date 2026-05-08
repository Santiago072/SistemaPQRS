FROM php:8.2-fpm

# Instalar extensiones de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar Nginx
RUN apt-get update && apt-get install -y nginx \
    && rm -rf /var/lib/apt/lists/*

# Configurar Nginx
RUN echo 'server { \
    listen 80; \
    server_name localhost; \
    root /var/www/html; \
    index index.php index.html; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
}' > /etc/nginx/sites-available/default

# Script de inicio que corre ambos servicios
RUN echo '#!/bin/bash\n\
php-fpm --daemonize\n\
nginx -g "daemon off;"' > /start.sh && chmod +x /start.sh

# Copiar archivos del proyecto
COPY . /var/www/html/

# Railway usa la variable PORT, pero Nginx escucha en 80
# Creamos un proxy simple si es necesario
ENV PORT=80
EXPOSE 80

CMD ["/start.sh"]