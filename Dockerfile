FROM php:8.3-fpm-alpine

# Install nginx and supervisor
RUN apk update && apk add --no-cache nginx supervisor

# Configure nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configure supervisor to run both php-fpm and nginx
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Install php extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]