#!/usr/bin/env bash
echo "Caching config, routes, and views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations"
php artisan migrate --force

echo "Linking storage"
php artisan storage:link --force