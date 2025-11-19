FROM php:8.2-apache

# Cài extension cần thiết
RUN apt-get update && apt-get install -y zip unzip libzip-dev \
    && docker-php-ext-install pdo pdo_mysql

# Copy mã nguồn vào container
COPY src/ /var/www/html/

# Cấp quyền cho Apache đọc file
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Bật mod_rewrite (cho .htaccess nếu có)
RUN a2enmod rewrite

# ✅ Đặt DocumentRoot trỏ đến thư mục "public"
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && echo '<Directory "/var/www/html/public">\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/docker-allow.conf \
    && a2enconf docker-allow

# Mở cổng 80
EXPOSE 80

# Chạy Apache
CMD ["apache2-foreground"]
