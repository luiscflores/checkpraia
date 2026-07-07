#!/bin/bash
set -euo pipefail

APP_DIR="${1:-/home/pi/checkpraia}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
SUPERVISOR_PROGRAM="${SUPERVISOR_PROGRAM:-checkpraia-worker}"

cd "$APP_DIR"

echo ">>> Deploy: $APP_DIR"

git fetch origin main
git reset --hard origin/main

mkdir -p storage bootstrap/cache database

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        php artisan key:generate --no-interaction
        echo ">>> .env criado. Edita .env com as chaves de API antes de continuares."
        exit 1
    fi
fi

export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

npm ci --ignore-scripts --no-audit --no-fund
npm run build
rm -rf node_modules

php artisan migrate --force --seed
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --no-interaction 2>/dev/null || true

sudo chmod -R g+rwX storage bootstrap/cache database 2>/dev/null || true

sudo systemctl reload "$PHP_FPM_SERVICE" 2>/dev/null || sudo systemctl restart "$PHP_FPM_SERVICE"

sudo supervisorctl restart "$SUPERVISOR_PROGRAM:*" 2>/dev/null || echo ">>> Aviso: supervisorctl falhou. Verifica o supervisor."

echo ">>> Deploy concluido!"
