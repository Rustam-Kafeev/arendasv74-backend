FROM serversideup/php:8.3-fpm-nginx

# Копируем код приложения
COPY . /var/www/html
WORKDIR /var/www/html

# Устанавливаем права и зависимости
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Права на storage и bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Переменные окружения
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_REVALIDATE_FREQ=0

# Открываем порт
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]