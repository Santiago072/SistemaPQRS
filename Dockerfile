FROM php:8.2-fpm

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar Nginx y supervisor
RUN apt-get update && apt-get install -y nginx supervisor \
    && rm -rf /var/lib/apt/lists/*

# Configurar Nginx
RUN echo 'server { \
    listen 80; \
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

# Configurar supervisor para correr ambos procesos
RUN echo '[supervisord]\n\
nodaemon=true\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true' > /etc/supervisor/conf.d/supervisord.conf

# Copiar archivos del proyecto
COPY . /var/www/html/

# Exponer puerto 80 (Railway detectará este puerto)
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]