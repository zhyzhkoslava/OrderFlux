#!/bin/sh
set -e

# -----------------------------
# 1. Генеруємо ключ (якщо ще не згенеровано)
# -----------------------------
if [ ! -f /app/.env ]; then
    cp /app/.env.example /app/.env
fi

php artisan key:generate || true

# -----------------------------
# 2. Встановлюємо Octane (якщо ще не встановлено)
# -----------------------------
if ! composer show laravel/octane > /dev/null 2>&1; then
    composer require laravel/octane --dev --no-interaction
    php artisan octane:install
fi

# -----------------------------
# 3. Виконуємо міграції (DEV)
# -----------------------------
php artisan migrate --force || true

# -----------------------------
# 4. Стартуємо Laravel Octane з FrankenPHP
# -----------------------------
exec php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000 --watch
