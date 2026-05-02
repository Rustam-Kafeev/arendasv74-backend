#!/bin/bash
echo "=== Остановка PostgreSQL ==="
/opt/local/lib/postgresql15/bin/pg_ctl -D /opt/local/var/db/postgresql15/defaultdb stop

echo "=== Остановка Laravel (если запущен) ==="
# Останавливаем все процессы artisan serve
pkill -f "artisan serve"

echo "Готово."