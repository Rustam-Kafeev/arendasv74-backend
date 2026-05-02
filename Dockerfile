FROM php:8.3-fpm-alpine

# Установка системных зависимостей и расширений
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Создаём директорию для логов supervisor
RUN mkdir -p /var/log/supervisor

# Копирование конфигурации Nginx и Supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Копирование кода приложения
COPY . /var/www/html

# Установка PHP-зависимостей
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Настройка прав
RUN chown -R www-data:www-data storage bootstrap/cache

# Автоматический запуск миграций при старте контейнера
RUN echo '#!/bin/sh' > /entrypoint.sh && \
    echo 'php artisan migrate --force' >> /entrypoint.sh && \
    echo 'php artisan storage:link --force' >> /entrypoint.sh && \
    echo 'exec supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /entrypoint.sh && \
    chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]