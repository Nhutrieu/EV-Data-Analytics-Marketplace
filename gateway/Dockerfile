<<<<<<< HEAD
FROM php:8.2-apache

WORKDIR /var/www/html
COPY index.php /var/www/html/index.php
EXPOSE 80
=======
# Stage 0: PHP + Apache + Composer
FROM php:8.2-apache

# Cài các extension cần thiết
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Thiết lập working directory
WORKDIR /var/www/html

# Copy toàn bộ project (src + composer.json)
COPY . .

# Cài các package PHP (guzzle...)
RUN composer install

# Mở port 80 cho Apache
EXPOSE 80

# Chỉ định entrypoint
CMD ["apache2-foreground"]
>>>>>>> b2b62b30502933ce69778733bd55f2cea706a8e3
