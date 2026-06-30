#!/bin/bash
set -e

if [ ! -f .env ]; then
    touch .env
fi

php artisan key:generate --no-interaction

php artisan storage:link --no-interaction 2>/dev/null || true

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

php artisan migrate --no-interaction --force

exec apache2-foreground
