FROM php:8.2-cli

# Set working directory
WORKDIR /var/www

# Install dependencies & mysqli
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev && \
    docker-php-ext-install mysqli

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy code
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --prefer-dist

# Expose port (match PHP local dev port)
EXPOSE 8001

# Start PHP server
CMD ["php", "-S", "0.0.0.0:8001", "-t", "src"]
