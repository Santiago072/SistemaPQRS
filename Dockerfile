FROM php:8.2-fpm

# Instalar extensiones de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar Nginx y supervisor
RUN apt-get update && apt-get install -y nginx supervisor \
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

# Configurar supervisor
RUN mkdir -p /etc/supervisor/conf.d && \
    echo '[supervisord] \
    nodaemon=true \
    user=root \
    [program:php-fpm] \
    command=php-fpm \
    autostart=true \
    autorestart=true \
    [program:nginx] \
    command=nginx -g "daemon off;" \
    autostart=true \
    autorestart=true' > /etc/supervisor/conf.d/supervisord.conf

# Copiar archivos
COPY . /var/www/html/

# Puerto 80
EXPOSE 80

# Iniciar supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]