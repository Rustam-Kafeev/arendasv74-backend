#!/bin/bash
echo "=== Запуск PostgreSQL ==="
# Запускаем без указания лог-файла (вывод в терминал)
/opt/local/lib/postgresql15/bin/pg_ctl -D /opt/local/var/db/postgresql15/defaultdb start

# Даём базе пару секунд на старт
sleep 2

echo "=== Запуск Laravel ==="
php artisan serve --host=0.0.0.0 --port=8000