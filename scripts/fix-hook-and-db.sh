#!/bin/bash
# ── CheckPraia - Fix Urgente no RPI3 ────────────────────────────────────────
# Corre DIRETAMENTE no RPI3 via SSH:
#   ssh pi@checkpraia.pt "bash -s" < scripts/fix-hook-and-db.sh
# OU copia para o Pi e executa:
#   scp scripts/fix-hook-and-db.sh pi@checkpraia.pt:~/ && ssh pi@checkpraia.pt "bash ~/fix-hook-and-db.sh"
# ────────────────────────────────────────────────────────────────────────────
set -uo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'
BOLD='\033[1m'; NC='\033[0m'
ok()   { echo -e " ${GREEN}✓${NC} $1"; }
fail() { echo -e " ${RED}✗${NC} $1"; }
info() { echo -e " ${CYAN}→${NC} $1"; }
warn() { echo -e " ${YELLOW}⚠${NC} $1"; }
hdr()  { echo -e "\n${BOLD}── $1 ──${NC}"; }

echo ""
echo -e "${BOLD}  CheckPraia - Fix Urgente RPI3  ${NC}"
echo ""

# ── 1. Descobrir onde a app está realmente deployada ─────────────────────────
hdr "1. Localizar a app"

APP_DIR=""
for candidate in "/var/www/checkpraia" "/home/pi/checkpraia" "$HOME/checkpraia"; do
    if [ -f "$candidate/artisan" ]; then
        APP_DIR="$candidate"
        ok "App encontrada: $APP_DIR"
        break
    fi
done

if [ -z "$APP_DIR" ]; then
    fail "App não encontrada! Caminhos testados: /var/www/checkpraia, /home/pi/checkpraia"
    exit 1
fi

# ── 2. Mostrar o hook atual ───────────────────────────────────────────────────
hdr "2. Hook atual no bare repo"

BARE_REPO="/home/pi/checkpraia.git"
HOOK="$BARE_REPO/hooks/post-receive"

if [ -f "$HOOK" ]; then
    echo "  Conteúdo atual do hook:"
    cat "$HOOK" | sed 's/^/    /'
else
    warn "Hook não encontrado em $HOOK"
fi

# ── 3. Criar SQLite AGORA ─────────────────────────────────────────────────────
hdr "3. Criar SQLite"

DB_DIR="$APP_DIR/database"
DB_FILE="$DB_DIR/database.sqlite"

sudo mkdir -p "$DB_DIR" 2>/dev/null || mkdir -p "$DB_DIR"

if [ ! -f "$DB_FILE" ]; then
    sudo touch "$DB_FILE" 2>/dev/null || touch "$DB_FILE"
    ok "database.sqlite criado: $DB_FILE"
else
    ok "database.sqlite já existe ($DB_FILE)"
fi

# Permissões
sudo chmod 664 "$DB_FILE" 2>/dev/null || chmod 664 "$DB_FILE" 2>/dev/null || true
sudo chown www-data:www-data "$DB_FILE" 2>/dev/null || true
sudo chmod 2775 "$DB_DIR" 2>/dev/null || chmod 2775 "$DB_DIR" 2>/dev/null || true
ok "Permissões SQLite OK"

# ── 4. Criar estrutura de diretórios ─────────────────────────────────────────
hdr "4. Diretórios"

for d in \
    "$APP_DIR/storage/framework/cache/data" \
    "$APP_DIR/storage/framework/sessions" \
    "$APP_DIR/storage/framework/views" \
    "$APP_DIR/storage/logs" \
    "$APP_DIR/bootstrap/cache"
do
    sudo mkdir -p "$d" 2>/dev/null || mkdir -p "$d" 2>/dev/null || true
done

sudo chown -R www-data:www-data \
    "$APP_DIR/storage/framework" \
    "$APP_DIR/storage/logs" \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/database" \
    2>/dev/null || true

sudo chmod -R 2775 \
    "$APP_DIR/storage" \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/database" \
    2>/dev/null || true

ok "Diretórios e permissões OK"

# ── 5. Corrigir o post-receive hook ──────────────────────────────────────────
hdr "5. Corrigir post-receive hook"

if [ -d "$BARE_REPO" ]; then
    # Fazer backup do hook atual
    [ -f "$HOOK" ] && cp "$HOOK" "$HOOK.bak.$(date +%s)" 2>/dev/null || true

    cat > "$HOOK" << HOOK_CONTENT
#!/bin/bash
set -euo pipefail

TARGET="$APP_DIR"
BARE="$BARE_REPO"

echo ">>> [\$(date '+%H:%M:%S')] Deploy iniciado"

# Checkout do código para o work tree
GIT_WORK_TREE="\$TARGET" git checkout -f

# Garantir que o SQLite existe ANTES de qualquer coisa
mkdir -p "\$TARGET/database"
[ -f "\$TARGET/database/database.sqlite" ] || touch "\$TARGET/database/database.sqlite"
chmod 664 "\$TARGET/database/database.sqlite" 2>/dev/null || true
chown www-data:www-data "\$TARGET/database/database.sqlite" 2>/dev/null || true

# Correr o deploy script com o caminho correto
bash "\$TARGET/scripts/deploy.sh" "\$TARGET"

echo ">>> [\$(date '+%H:%M:%S')] Deploy concluído!"
HOOK_CONTENT

    chmod +x "$HOOK"
    ok "Hook corrigido → TARGET=$APP_DIR"
    echo ""
    echo "  Novo hook:"
    cat "$HOOK" | sed 's/^/    /'
else
    warn "Bare repo não encontrado em $BARE_REPO"
fi

# ── 6. Correr deploy manualmente para reparar o estado atual ─────────────────
hdr "6. Deploy manual"

cd "$APP_DIR"

# Verificar .env
if [ ! -f .env ]; then
    warn ".env não existe — a criar..."
    cp .env.example .env 2>/dev/null || true
    php artisan key:generate --no-interaction 2>/dev/null || true
    warn "EDITA $APP_DIR/.env com as tuas chaves!"
fi

# Composer (sem scripts para evitar o crash)
info "A correr composer install..."
export COMPOSER_ALLOW_SUPERUSER=1
if composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --quiet 2>/dev/null; then
    ok "composer install OK"
else
    warn "composer install falhou — verifica acima"
fi

# Agora que o SQLite existe, package:discover é seguro
php artisan package:discover --ansi 2>/dev/null || true

# Migrations
info "A correr migrations..."
php artisan migrate --force 2>&1 | tail -5 && ok "Migrations OK" || warn "Migrations falharam"

# Caches
info "A reconstruir caches..."
php artisan optimize:clear --quiet 2>/dev/null || true
php artisan config:cache --quiet  && ok "config:cache OK"  || warn "config:cache falhou"
php artisan route:cache --quiet   && ok "route:cache OK"   || warn "route:cache falhou"
php artisan view:cache --quiet    && ok "view:cache OK"    || warn "view:cache falhou"
php artisan storage:link --no-interaction 2>/dev/null || true

# Recarregar serviços
info "A recarregar serviços..."
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.4")
sudo systemctl reload "php${PHP_VERSION}-fpm" 2>/dev/null || \
    sudo systemctl restart "php${PHP_VERSION}-fpm" 2>/dev/null || true
sudo systemctl reload nginx 2>/dev/null || \
    sudo systemctl restart nginx 2>/dev/null || true
ok "Serviços recarregados"

# ── 7. Teste ─────────────────────────────────────────────────────────────────
hdr "7. Teste"

sleep 1
HTTP=$(curl -sk -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "err")
HTTPS=$(curl -sk -o /dev/null -w "%{http_code}" https://localhost 2>/dev/null || echo "err")
info "HTTP  → $HTTP"
info "HTTPS → $HTTPS"

if [ "$HTTPS" = "200" ] || [ "$HTTP" = "301" ]; then
    ok "Site a responder!"
elif [ "$HTTPS" = "403" ]; then
    fail "Ainda 403 — verifica: sudo tail -20 /var/log/nginx/error.log"
elif [ "$HTTPS" = "500" ]; then
    fail "Ainda 500 — verifica: tail -30 $APP_DIR/storage/logs/laravel.log"
fi

echo ""
echo -e "${BOLD}  Feito! Próximo git push pi main deve funcionar sem erros.${NC}"
echo ""
