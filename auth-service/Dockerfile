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
