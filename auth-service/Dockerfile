<<<<<<< HEAD
# Sử dụng image PHP Apache
FROM php:8.2-apache

# Cài PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copy source code
COPY src/ /var/www/src/
COPY public/ /var/www/html/

# Copy entrypoint script
COPY entrypoint.sh /entrypoint.sh

# Cấp quyền thực thi cho script (chạy trong môi trường Linux của Docker)
RUN chmod +x /entrypoint.sh

# Set working directory
WORKDIR /var/www/html

# Enable mod_rewrite nếu cần
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Dùng entrypoint custom để kiểm soát khi gateway chết
ENTRYPOINT ["/entrypoint.sh"]
=======
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
>>>>>>> b2b62b30502933ce69778733bd55f2cea706a8e3
