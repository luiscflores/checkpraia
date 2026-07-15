#!/bin/bash
# ── CheckPraia - Setup Nativo Raspberry Pi 3 (Production) ──────────────────
# Internet-exposed. Includes SSL, firewall, rate limiting, hardening.
set -euo pipefail

PI_USER="${PI_USER:-pi}"
PI_DIR="/home/$PI_USER"
WORK_TREE="$PI_DIR/checkpraia"
BARE_REPO="$PI_DIR/checkpraia.git"
PHP_VERSION="8.4"
LOG_FILE="/tmp/checkpraia-setup.log"

log()  { echo ">>> $(date '+%H:%M:%S') $*" | tee -a "$LOG_FILE"; }
fail() { log "FAILED: $*"; exit 1; }

echo ""
echo "============================================"
echo "  Setup Nativo - CheckPraia no Raspberry Pi"
echo "  Production: Internet-Exposed"
echo "============================================"
echo ""

# ── 0. Pre-flight: enable swap ───────────────────────────────────────────
log "=== 0. Garantir swap (critico para RPi3 1GB RAM) ==="

if [ ! -f /swapfile ]; then
    sudo fallocate -l 512M /swapfile 2>/dev/null || sudo dd if=/dev/zero of=/swapfile bs=1M count=512 2>/dev/null
    sudo chmod 600 /swapfile
    sudo mkswap /swapfile
    sudo swapon /swapfile
    echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
    log "Swap 512MB criado."
else
    log "Swap ja existe."
fi

if ! grep -q "vm.swappiness=10" /etc/sysctl.conf 2>/dev/null; then
    echo "vm.swappiness=10" | sudo tee -a /etc/sysctl.conf
    sudo sysctl vm.swappiness=10 2>/dev/null || true
fi

# ── 1. Instalar dependencias do sistema ──────────────────────────────────
log "=== 1. Instalar dependencias do sistema ==="

sudo apt-get update -y -qq
sudo apt-get install -y -qq \
    curl wget git unzip nginx supervisor sqlite3 cron \
    certbot python3-certbot-nginx \
    build-essential 2>/dev/null || true

# ── 2. Instalar PHP 8.4 (sury repository) ────────────────────────────────
log "=== 2. Instalar PHP $PHP_VERSION ==="

if ! command -v php &>/dev/null || ! php -v 2>/dev/null | grep -q "$PHP_VERSION"; then
    sudo curl -sSL https://packages.sury.org/php/apt.gpg -o /usr/share/keyrings/sury-php.gpg
    echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" \
        | sudo tee /etc/apt/sources.list.d/sury-php.list > /dev/null
    sudo apt-get update -y -qq
    sudo apt-get install -y -qq \
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
log "=== 3. Instalar Composer ==="

if ! command -v composer &>/dev/null; then
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
    log "Composer installed."
fi

# ── 4. Instalar Node.js ─────────────────────────────────────────────────
log "=== 4. Instalar Node.js ==="

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
    log "Node.js $(node --version) installed."
fi

# ── 5. Configurar OPCache + JIT ──────────────────────────────────────────
log "=== 5. Configurar OPCache + JIT ==="

sudo cp "$WORK_TREE/scripts/php-opcache-jit.ini" /etc/php/${PHP_VERSION}/mods-available/10-opcache.ini 2>/dev/null \
    || sudo cp scripts/php-opcache-jit.ini /etc/php/${PHP_VERSION}/mods-available/10-opcache.ini 2>/dev/null \
    || true
sudo phpenmod opcache 2>/dev/null || true

# ── 6. Configurar nginx ──────────────────────────────────────────────────
log "=== 6. Configurar nginx ==="

# Rate limit zone (must be in http context, before server blocks)
sudo tee /etc/nginx/snippets/rate-limit.conf > /dev/null << 'EOF'
limit_req_zone $binary_remote_addr zone=general:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=auth:10m rate=2r/s;
limit_req_status 429;
EOF

# Include rate-limit in nginx.conf http block
if ! grep -q "rate-limit.conf" /etc/nginx/nginx.conf 2>/dev/null; then
    sudo sed -i '/http {/a \    include /etc/nginx/snippets/rate-limit.conf;' /etc/nginx/nginx.conf 2>/dev/null || true
fi

sudo cp "$WORK_TREE/scripts/checkpraia-nginx.conf" /etc/nginx/sites-available/checkpraia 2>/dev/null \
    || sudo cp scripts/checkpraia-nginx.conf /etc/nginx/sites-available/checkpraia 2>/dev/null \
    || true

sudo rm -f /etc/nginx/sites-enabled/default
sudo ln -sf /etc/nginx/sites-available/checkpraia /etc/nginx/sites-enabled/

# Validate nginx config before restarting
sudo nginx -t 2>/dev/null && log "Nginx config OK." || log "WARNING: nginx config test failed!"

# ── 7. Clonar repositorio ───────────────────────────────────────────────
log "=== 7. Clonar repositorio ==="

if [ ! -d "$WORK_TREE" ]; then
    git clone --depth 1 https://github.com/luiscflores/checkpraia.git "$WORK_TREE"
    log "Repo clonado (shallow) em $WORK_TREE"
fi

cd "$WORK_TREE"

mkdir -p storage bootstrap/cache database storage/logs
chmod -R 775 storage bootstrap/cache

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --no-interaction
    log ".env criado — edita-o: nano $WORK_TREE/.env"
fi

# ── 8. Instalar dependencias do projeto ──────────────────────────────────
log "=== 8. Instalar dependencias PHP/JS ==="

export COMPOSER_ALLOW_SUPERUSER=1
composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-suggest \
    --prefer-dist \
    --quiet

if [ -f package.json ]; then
    npm ci --ignore-scripts --no-audit --no-fund --prefer-offline --quiet 2>/dev/null
    npm run build --quiet 2>/dev/null
    rm -rf node_modules
fi

php artisan migrate --force --quiet 2>/dev/null || true
php artisan config:cache --quiet 2>/dev/null
php artisan route:cache --quiet 2>/dev/null
php artisan view:cache --quiet 2>/dev/null
php artisan event:cache --quiet 2>/dev/null
php artisan storage:link --no-interaction 2>/dev/null || true

# ── 9. Configurar Supervisor ──────────────────────────────────────────────
log "=== 9. Configurar Supervisor ==="

if [ -f "$WORK_TREE/checkpraia-worker.conf" ]; then
    sudo cp "$WORK_TREE/checkpraia-worker.conf" /etc/supervisor/conf.d/checkpraia-worker.conf
    sudo supervisorctl reread 2>/dev/null || true
    sudo supervisorctl update 2>/dev/null || true
fi

# ── 10. Configurar Cron ────────────────────────────────────────────────
log "=== 10. Configurar Cron ==="

CRON_SCHEDULER="* * * * * cd $WORK_TREE && php artisan schedule:run >> /dev/null 2>&1"
CRON_DEPLOY="*/5 * * * * cd $WORK_TREE && bash scripts/deploy.sh >> $WORK_TREE/storage/logs/deploy.log 2>&1"
CRON_CERT="0 3 * * * certbot renew --quiet --post-hook 'systemctl reload nginx'"

(crontab -u "$PI_USER" -l 2>/dev/null | grep -v "checkpraia" | grep -v "certbot renew"; \
 echo "$CRON_SCHEDULER"; echo "$CRON_DEPLOY"; echo "$CRON_CERT") \
    | crontab -u "$PI_USER" - 2>/dev/null || true

# ── 11. Bare git repo + post-receive ──────────────────────────────────────
log "=== 11. Configurar bare git repo ==="

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
    log "Bare repo criado em $BARE_REPO"
fi

# ── 12. Permissoes ──────────────────────────────────────────────────────
log "=== 12. Corrigir permissoes ==="

sudo usermod -aG www-data "$PI_USER" 2>/dev/null || true
sudo chmod +x "/home/$PI_USER" 2>/dev/null || true
sudo chown -R "$PI_USER":www-data "$WORK_TREE"
sudo chmod -R 2775 "$WORK_TREE/storage" "$WORK_TREE/bootstrap/cache" "$WORK_TREE/database"

# ── 13. SSL placeholder (self-signed) + Arrancar servicos ───────────────
log "=== 13. Arrancar servicos ==="

# Generate self-signed cert so nginx can start before Let's Encrypt is ready
CERT_DIR="/etc/letsencrypt/live/checkpraia.pt"
if [ ! -f "$CERT_DIR/fullchain.pem" ]; then
    log "A gerar certificado self-signed como placeholder..."
    sudo mkdir -p "$CERT_DIR"
    sudo openssl req -x509 -nodes -days 365 \
        -newkey rsa:2048 \
        -keyout "$CERT_DIR/privkey.pem" \
        -out "$CERT_DIR/fullchain.pem" \
        -subj "/CN=checkpraia.pt" 2>/dev/null
    sudo cp "$CERT_DIR/fullchain.pem" "$CERT_DIR/chain.pem"
    log "Certificado self-signed criado (será substituido pelo certbot)."
fi

sudo systemctl enable nginx php${PHP_VERSION}-fpm supervisor 2>/dev/null || true
sudo systemctl restart nginx php${PHP_VERSION}-fpm supervisor 2>/dev/null || true

# ── 14. SSL Certificate ─────────────────────────────────────────────────
log "=== 14. Configurar SSL ==="

if [ ! -f /etc/letsencrypt/live/checkpraia.pt/fullchain.pem ]; then
    log "SSL certificate NAO encontrado."
    log ">>> Depois de configurar DNS para o IP publico, correr:"
    log "    sudo certbot --nginx -d checkpraia.pt"
    log ">>> Ou se DNS ainda nao propagou:"
    log "    sudo certbot certonly --standalone -d checkpraia.pt"
else
    log "SSL certificate encontrado."
    sudo systemctl enable certbot.timer 2>/dev/null || true
    sudo systemctl start certbot.timer 2>/dev/null || true
fi

# ── 15. Security hardening ──────────────────────────────────────────────
log "=== 15. Security hardening ==="

if [ -f "$WORK_TREE/scripts/security-hardening.sh" ]; then
    bash "$WORK_TREE/scripts/security-hardening.sh"
fi

# ── Done ────────────────────────────────────────────────────────────────
echo ""
echo "============================================"
echo "  Setup completo! (Production-Ready)"
echo "============================================"
echo ""
echo "  App:        https://checkpraia.pt"
echo "  Directorio: $WORK_TREE"
echo ""
echo "  PROXIMO PASSO (obrigatorio):"
echo "    1. Editar .env:     nano $WORK_TREE/.env"
echo "    2. Configurar DNS:  apontar checkpraia.pt para o IP publico do RPi"
echo "    3. Pedir SSL:       sudo certbot --nginx -d checkpraia.pt"
echo ""
echo "  Deploy via git push:"
echo "    git remote add pi ssh://pi@<IP_PUBLICO>/home/pi/checkpraia.git"
echo "    git push pi main"
echo ""
echo "  Nota: O cron (a cada 5min) faz pull automatico do GitHub."
echo ""
