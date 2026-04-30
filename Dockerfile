FROM php:8.3-fpm-alpine

# Установка системных зависимостей
RUN apk update && apk add --no-cache \
    libpq-dev \
    nginx \
    supervisor \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копирование конфигурации Nginx и Supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

# Копирование кода приложения
COPY . /var/www/html

# Установка PHP-зависимостей
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Настройка прав
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]