FROM php:8.2-apache

# Desactivar mpm_event y activar mpm_prefork (soluciona el error de MPM)
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto
COPY . /var/www/html/

# Exponer puerto 80
EXPOSE 80