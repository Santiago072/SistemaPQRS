FROM php:8.2-apache

# Instalar extensiones de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Desactivar mpm_event y activar mpm_prefork correctamente
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork rewrite

# Configurar Apache para escuchar en el puerto que Railway asigne
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/' /etc/apache2/sites-available/000-default.conf

# Habilitar .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copiar archivos del proyecto
COPY . /var/www/html/

# Puerto dinámico (Railway asigna PORT)
EXPOSE 80

# Script de inicio que reemplaza ${PORT} por el valor real
CMD ["sh", "-c", "export PORT=${PORT:-80} && sed -i \"s/\\${PORT}/$PORT/g\" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && apache2-foreground"]