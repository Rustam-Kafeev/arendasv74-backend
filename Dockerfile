FROM richarvey/nginx-php-fpm:3.1.6
COPY . .

# Убираем SKIP_COMPOSER, чтобы Render НЕ отключал установку зависимостей
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1

# Явно устанавливаем зависимости Composer при сборке
RUN composer install --no-dev --optimize-autoloader --no-interaction

CMD ["/start.sh"]