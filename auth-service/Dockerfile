FROM php:8.2-apache

# Cài extension cần thiết
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Thiết lập working directory
WORKDIR /var/www/html

# Copy toàn bộ source + composer.json
COPY . .

# Cài các package PHP
RUN composer install

EXPOSE 80
CMD ["apache2-foreground"]
