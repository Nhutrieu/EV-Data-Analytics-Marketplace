FROM php:8.2-apache

# Cài PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Bật mod_rewrite (nếu dùng .htaccess)
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . /var/www/html
