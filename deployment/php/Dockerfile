# Utilizar la imagen base de PHP 8.2 con Alpine
FROM php@sha256:c0fca44d0e3f6bf9555bb8bafa2402fe278f0f947afc4a30729deba5e3f50410

# Instalar Nginx y dependencias del sistema
RUN apk update && apk add nginx openssl bash mysql-client nodejs npm mariadb-connector-c-dev \
    && mkdir -p /run/nginx

# Copiar la configuración de Nginx
COPY ./deployment/nginx/conf/default.conf /etc/nginx/http.d/default.conf

# Instalar dependencias adicionales y extensiones de PHP
RUN apk add --no-cache libzip-dev zlib-dev libpng-dev gmp-dev \
    && docker-php-ext-install bcmath gd zip pdo pdo_mysql

# Instalar Composer globalmente
#COPY --from=composer@sha256:2dc4166e6ef310e16a9ab898e6bd5d088d1689f75f698559096d962b12c889cc /usr/bin/composer /usr/bin/composer

# Configurar el directorio de trabajo
WORKDIR /var/www

# Copiar la aplicación al contenedor
COPY . /var/www

RUN rm -rf /var/www/deployment

# Asignar permisos adecuados
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 
# && composer install --no-dev --optimize-autoloader 
# && npm install && npm run build
# Exponer los puertos de Nginx y PHP-FPM
EXPOSE 80 9000

# Comando para ejecutar al iniciar el contenedor
CMD ["sh", "-c", "nginx && php-fpm"]
