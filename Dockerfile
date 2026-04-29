FROM trafex/php-nginx:php8.3

# Копируем приложение
COPY . /var/www/html
WORKDIR /var/www/html

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем зависимости Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Права на storage и bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Скрипт, который выполнится при старте контейнера (запустит миграции и т.д.)
COPY scripts/00-laravel-deploy.sh /docker-entrypoint-init.d/00-laravel-deploy.sh
RUN chmod +x /docker-entrypoint-init.d/00-laravel-deploy.sh

# Подставляем CORS и APP_KEY (настройку CORS мы уже положили в config/cors.php)
# Экспортируем порт
EXPOSE 80