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

# Копируем скрипт и делаем его исполняемым
COPY scripts/00-laravel-deploy.sh /usr/local/bin/deploy.sh
RUN chmod +x /usr/local/bin/deploy.sh

# Автоматический запуск скрипта при старте контейнера
ENTRYPOINT ["/usr/local/bin/deploy.sh"]