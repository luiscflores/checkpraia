#!/bin/bash
set -e

if [ ! -f .env ] && [ -z "$APP_KEY" ]; then
    touch .env
    php artisan key:generate --no-interaction
fi

php artisan storage:link --no-interaction 2>/dev/null || true

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

php artisan migrate --no-interaction --force

exec apache2-foreground
