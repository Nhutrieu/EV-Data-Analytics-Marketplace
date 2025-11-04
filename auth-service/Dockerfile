FROM php:8.2-fpm

RUN docker-php-ext-install pdo pdo_mysql mysqli
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-interaction --no-scripts --no-progress

EXPOSE 9000
CMD ["php-fpm"]
