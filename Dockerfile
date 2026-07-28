# Use PHP 8.2 FPM Alpine as the base image for a lightweight footprint
FROM php:8.2-fpm-alpine

# Install system dependencies and PostgreSQL client libraries
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libpq \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev

# Install PHP extensions required for Laravel and PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql bcmath mbstring xml gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application source code
COPY . .

# Install PHP dependencies (production optimized)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set correct permissions for Laravel storage and bootstrap cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy Nginx and Supervisor configurations
COPY ./docker/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf

# Copy the entrypoint script
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80 for Render routing
EXPOSE 80

# Execute entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
