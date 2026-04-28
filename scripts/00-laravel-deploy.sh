#!/usr/bin/env bash
echo "Installing composer dependencies"
composer install --no-dev --optimize-autoloader

echo "Caching config, routes, and views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations"
php artisan migrate --force

echo "Linking storage"
php artisan storage:link --force