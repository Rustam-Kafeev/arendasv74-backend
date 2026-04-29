FROM php:8.3-cli

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    nginx \
    supervisor

# Устанавливаем PHP расширения
RUN docker-php-ext-install pdo pdo_pgsql

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем код приложения
COPY . /app
WORKDIR /app

# Устанавливаем зависимости Laravel
RUN composer install --no-dev --optimize-autoloader

# Права на storage и bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Порт, который будет слушать Railway (переменная окружения)
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]