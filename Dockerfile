FROM php:8.1-apache

# Instalar extensiones
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configurar Apache para el puerto dinámico de Railway
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/' /etc/apache2/sites-available/000-default.conf

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar archivos
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

# Script de inicio que configura el puerto dinámico
RUN echo '#!/bin/bash\n\
PORT=${PORT:-80}\n\
sed -i "s/\\${PORT}/$PORT/g" /etc/apache2/ports.conf\n\
sed -i "s/\\${PORT}/$PORT/g" /etc/apache2/sites-available/000-default.conf\n\
echo "ServerName localhost" >> /etc/apache2/apache2.conf\n\
apache2-foreground' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]