FROM php:8.3-fpm-alpine

RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

RUN mkdir -p /var/log/supervisor

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN chown -R www-data:www-data storage bootstrap/cache

# Прямая команда: миграции, сидер, storage:link, затем запуск supervisor
CMD php artisan migrate --force && php artisan db:seed --class=CitySeeder --force && php artisan storage:link --force && supervisord -c /etc/supervisor/conf.d/supervisord.conf