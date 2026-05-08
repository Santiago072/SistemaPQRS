FROM php:8.2-cli

# Instalar extensiones de PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto
COPY . /var/www/html/

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Puerto que usará el servidor
ENV PORT=80
EXPOSE 80

# Iniciar servidor PHP integrado en el puerto correcto
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-80}"]