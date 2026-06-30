#!/bin/bash
set -e

# Cria .env vazio se não existir (as vars vêm do ambiente do Render)
if [ ! -f .env ]; then
    touch .env
fi

# Gera APP_KEY apenas se não estiver definida nas env vars do Render
php artisan key:generate

# Cria link storage
php artisan storage:link --force 2>/dev/null || true

# Cache de config, rotas e views
php artisan config:cache --force
php artisan route:cache --force
php artisan view:cache --force

# Corre migrações
php artisan migrate --force

# Inicia o Apache
exec apache2-foreground
