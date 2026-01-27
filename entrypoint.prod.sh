#!/bin/sh
set -e

# -----------------------------
# 1. Generate APP key if it does not exist
# -----------------------------
if [ ! -f /app/.env ]; then
    cp /app/.env.example /app/.env
fi

php artisan key:generate || true

# -----------------------------
# 2. Run database migrations
# -----------------------------
echo "Running migrations..."
php artisan migrate --force || true

# -----------------------------
# 3. Optimize Laravel caches (optional but recommended)
# -----------------------------
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# -----------------------------
# 4. Start Octane without watch mode
# -----------------------------
exec php artisan octane:start --server=frankenphp --host=0.0.0.0 --port=8000
