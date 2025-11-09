FROM php:8.2-apache

# Cài extension PHP cần cho MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite

WORKDIR /var/www/html

# Copy TOÀN BỘ đồ án vào container
COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html
