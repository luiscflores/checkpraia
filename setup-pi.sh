#!/bin/bash
set -euo pipefail

PI_USER="${PI_USER:-pi}"
PI_DIR="/home/$PI_USER"
WORK_TREE="$PI_DIR/checkpraia"
BARE_REPO="$PI_DIR/checkpraia.git"
PHP_VERSION="8.4"

echo ""
echo "============================================"
echo "  Setup Nativo - CheckPraia no Raspberry Pi"
echo "============================================"
echo ""

# ── 1. Instalar dependencias do sistema ──────────────────────────────────
echo "=== 1. Instalar dependencias do sistema ==="

sudo apt-get update -y

sudo apt-get install -y curl wget git unzip nginx supervisor sqlite3 cron

# ── 2. Instalar PHP 8.4 (sury repository) ────────────────────────────────
echo "=== 2. Instalar PHP $PHP_VERSION ==="

if ! command -v php &>/dev/null || ! php -v | grep -q "$PHP_VERSION"; then
    sudo curl -sSL https://packages.sury.org/php/apt.gpg -o /usr/share/keyrings/sury-php.gpg
    echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list
    sudo apt-get update -y
    sudo apt-get install -y \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-common \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip \
        php${PHP_VERSION}-sqlite3 \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-opcache \
        php${PHP_VERSION}-bcmath \
        php${PHP_VERSION}-json
fi

# ── 3. Instalar Composer ─────────────────────────────────────────────────
echo "=== 3. Instalar Composer ==="

if ! command -v composer &>/dev/null; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
    echo "Composer installed."
fi

# ── 4. Instalar Node.js + npm ────────────────────────────────────────────
echo "=== 4. Instalar Node.js ==="

if ! command -v node &>/dev/null; then
    ARCH=$(uname -m)
    if [ "$ARCH" = "armv7l" ] || [ "$ARCH" = "armhf" ]; then
        NODE_URL="https://nodejs.org/dist/v22.14.0/node-v22.14.0-linux-armv7l.tar.xz"
    elif [ "$ARCH" = "aarch64" ]; then
        NODE_URL="https://nodejs.org/dist/v22.14.0/node-v22.14.0-linux-arm64.tar.xz"
    else
        NODE_URL="https://nodejs.org/dist/v22.14.0/node-v22.14.0-linux-x64.tar.xz"
    fi
    curl -fsSL "$NODE_URL" | tar -xJ -C /tmp
    sudo cp -a /tmp/node-v22.14.0-linux-*/bin/* /usr/local/bin/
    sudo cp -a /tmp/node-v22.14.0-linux-*/lib/node_modules /usr/local/lib/
    rm -rf /tmp/node-v22.14.0-linux-*
    echo "Node.js $(node --version) installed."
fi

# ── 5. Configurar OPCache + JIT ──────────────────────────────────────────
echo "=== 5. Configurar OPCache + JIT ==="

sudo cp scripts/php-opcache-jit.ini /etc/php/${PHP_VERSION}/mods-available/10-opcache.ini 2>/dev/null || true
sudo phpenmod opcache 2>/dev/null || true

# ── 6. Configurar nginx ──────────────────────────────────────────────────
echo "=== 6. Configurar nginx ==="

sudo cp scripts/checkpraia-nginx.conf /etc/nginx/sites-available/checkpraia
sudo rm -f /etc/nginx/sites-enabled/default
sudo ln -sf /etc/nginx/sites-available/checkpraia /etc/nginx/sites-enabled/

# ── 7. Clonar repositorio (primeira vez) ──────────────────────────────────
echo "=== 7. Clonar repositorio ==="

if [ ! -d "$WORK_TREE" ]; then
    git clone https://github.com/luiscflores/checkpraia.git "$WORK_TREE"
    echo "Repo clonado em $WORK_TREE"
fi

cd "$WORK_TREE"

mkdir -p storage bootstrap/cache database
chmod -R 775 storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --no-interaction
    echo ">>> .env criado a partir de .env.example."
    echo ">>> EDITA O .ENV com as chaves de API primeiro:"
    echo "    nano $WORK_TREE/.env"
fi

# ── 8. Instalar dependencias do projeto ──────────────────────────────────
echo "=== 8. Instalar dependencias PHP/JS ==="

export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

if [ -f package.json ]; then
    npm ci --ignore-scripts --no-audit --no-fund
    npm run build
    rm -rf node_modules
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --no-interaction 2>/dev/null || true

# ── 9. Configurar Supervisor (queue worker) ──────────────────────────────
echo "=== 9. Configurar Supervisor ==="

sudo cp "$WORK_TREE/checkpraia-worker.conf" /etc/supervisor/conf.d/checkpraia-worker.conf
sudo supervisorctl reread
sudo supervisorctl update

# ── 10. Configurar Cron (Laravel scheduler + auto-deploy) ────────────────
echo "=== 10. Configurar Cron ==="

CRON_SCHEDULER="* * * * * cd $WORK_TREE && php artisan schedule:run >> /dev/null 2>&1"
CRON_DEPLOY="*/5 * * * * cd $WORK_TREE && bash scripts/deploy.sh >> $WORK_TREE/storage/logs/deploy.log 2>&1"

(crontab -u "$PI_USER" -l 2>/dev/null || true; echo "$CRON_SCHEDULER") | crontab -u "$PI_USER" -
(crontab -u "$PI_USER" -l 2>/dev/null || true; echo "$CRON_DEPLOY") | crontab -u "$PI_USER" -

# ── 11. Criar bare git repo + post-receive (deploy direto) ───────────────
echo "=== 11. Configurar bare git repo ==="

if [ ! -d "$BARE_REPO" ]; then
    git init --bare "$BARE_REPO"

    cat > "$BARE_REPO/hooks/post-receive" << 'HOOK'
#!/bin/bash
set -euo pipefail

TARGET="/home/pi/checkpraia"
cd "$TARGET"
git --work-tree="$TARGET" checkout -f
bash scripts/deploy.sh "$TARGET"

echo ">>> Deploy concluido!"
HOOK

    chmod +x "$BARE_REPO/hooks/post-receive"

    echo ""
    echo "Bare repo criado em $BARE_REPO"
    echo "Para fazer push direto: git push pi main"
fi

# ── 12. Corrigir permissoes para o nginx/www-data ────────────────────────
echo "=== 12. Corrigir permissoes ==="

sudo chmod +x "/home/$PI_USER"
sudo chown -R www-data:www-data "$WORK_TREE/storage" "$WORK_TREE/bootstrap/cache" "$WORK_TREE/database"
sudo chmod -R 775 "$WORK_TREE/storage" "$WORK_TREE/bootstrap/cache" "$WORK_TREE/database"

# ── 13. Arrancar servicos ────────────────────────────────────────────────
echo "=== 13. Arrancar servicos ==="

sudo systemctl enable nginx php${PHP_VERSION}-fpm supervisor
sudo systemctl restart nginx php${PHP_VERSION}-fpm supervisor

echo ""
echo "============================================"
echo "  Setup completo!"
echo "============================================"
echo ""
echo "  Aplicacao: http://192.168.1.212"
echo "  Diretorio: $WORK_TREE"
echo ""
echo "  Proximo passo:"
echo "    1. Editar .env: nano $WORK_TREE/.env"
echo "    2. Correr deploy: cd $WORK_TREE && bash scripts/deploy.sh"
echo ""
echo "  Para fazer push direto do teu PC:"
echo "    git push pi main"
echo ""
echo "  Ou espera que o cron (a cada 5min) faca pull do GitHub."
echo ""
