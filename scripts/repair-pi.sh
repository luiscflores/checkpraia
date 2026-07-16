#!/bin/bash
# ── CheckPraia - Reparação Completa RPI3 ────────────────────────────────────
# Resolve: 403 / 500, path mismatch /var/www vs /home/pi, SQLite em falta
# Corre no RPI3 como o user "pi":  bash ~/checkpraia/scripts/repair-pi.sh
#
# O que faz:
#   1. Detecta o caminho real da app
#   2. Cria/migra a base de dados SQLite
#   3. Corrige permissões
#   4. Reconstrói caches Laravel
#   5. Corrige o post-receive hook
#   6. Recarrega nginx + php-fpm
# ────────────────────────────────────────────────────────────────────────────
set -uo pipefail

PHP_VERSION="${PHP_VERSION:-8.4}"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'
BOLD='\033[1m'; NC='\033[0m'
ok()   { echo -e " ${GREEN}✓${NC} $1"; }
fail() { echo -e " ${RED}✗${NC} $1"; }
info() { echo -e " ${CYAN}→${NC} $1"; }
warn() { echo -e " ${YELLOW}⚠${NC} $1"; }
hdr()  { echo -e "\n${BOLD}── $1 ──${NC}"; }

echo ""
echo -e "${BOLD}============================================${NC}"
echo -e "${BOLD}  CheckPraia - Reparação RPI3              ${NC}"
echo -e "${BOLD}  $(date '+%Y-%m-%d %H:%M:%S')            ${NC}"
echo -e "${BOLD}============================================${NC}"

# ── 1. Detectar o caminho correto da app ─────────────────────────────────────
hdr "1. Localizar a app"

# Candidatos por ordem de preferência
CANDIDATES=(
    "/home/pi/checkpraia"
    "/var/www/checkpraia"
    "$HOME/checkpraia"
)

APP_DIR=""
for candidate in "${CANDIDATES[@]}"; do
    if [ -f "$candidate/artisan" ]; then
        APP_DIR="$candidate"
        ok "App encontrada em: $APP_DIR"
        break
    fi
done

if [ -z "$APP_DIR" ]; then
    fail "App não encontrada! Verifica se o git push foi bem feito."
    exit 1
fi

cd "$APP_DIR"

# ── 2. Verificar/criar .env ──────────────────────────────────────────────────
hdr "2. Ficheiro .env"

if [ ! -f .env ]; then
    warn ".env não existe — a criar a partir de .env.example..."
    cp .env.example .env
    php artisan key:generate --no-interaction
    ok ".env criado — EDITA-O com as tuas chaves antes de continuar!"
    echo ""
    echo -e "  ${RED}IMPORTANTE: edita $APP_DIR/.env${NC}"
    echo "  Pelo menos: APP_KEY (já gerado), APP_URL, etc."
    echo ""
else
    ok ".env existe"
    # Verificar APP_KEY
    APP_KEY=$(grep "^APP_KEY=" .env | cut -d= -f2- | tr -d '"' | tr -d "'")
    if [ -z "$APP_KEY" ]; then
        warn "APP_KEY está vazio — a gerar..."
        php artisan key:generate --no-interaction
        ok "APP_KEY gerado"
    else
        ok "APP_KEY está definido"
    fi
fi

# ── 3. Criar estrutura de diretórios ─────────────────────────────────────────
hdr "3. Estrutura de diretórios"

DIRS=(
    "storage"
    "storage/logs"
    "storage/framework"
    "storage/framework/cache"
    "storage/framework/cache/data"
    "storage/framework/sessions"
    "storage/framework/views"
    "bootstrap/cache"
    "database"
)

for dir in "${DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        mkdir -p "$dir"
        info "Criado: $dir"
    fi
done
ok "Estrutura de diretórios OK"

# ── 4. Base de dados SQLite ───────────────────────────────────────────────────
hdr "4. Base de Dados SQLite"

DB_PATH="$APP_DIR/database/database.sqlite"

if [ ! -f "$DB_PATH" ]; then
    info "A criar ficheiro SQLite..."
    touch "$DB_PATH"
    ok "database.sqlite criado"
else
    ok "database.sqlite já existe ($(du -h "$DB_PATH" | cut -f1))"
fi

# Permissões do SQLite
sudo chown www-data:www-data "$DB_PATH" 2>/dev/null || chown "$(whoami)":www-data "$DB_PATH" 2>/dev/null || true
chmod 664 "$DB_PATH"
ok "Permissões SQLite: 664 (www-data)"

# Permissão na pasta database também
sudo chown "$(whoami)":www-data "$APP_DIR/database" 2>/dev/null || true
chmod 2775 "$APP_DIR/database"

# Correr migrations
info "A correr migrations..."
if php artisan migrate --force 2>&1; then
    ok "Migrations concluídas"
else
    warn "Migrations falharam — verifica acima"
fi

# ── 5. Permissões globais ─────────────────────────────────────────────────────
hdr "5. Permissões"

# storage e bootstrap/cache precisam de ser writeable por www-data
sudo chown -R www-data:www-data \
    "$APP_DIR/storage/framework" \
    "$APP_DIR/storage/logs" \
    "$APP_DIR/bootstrap/cache" \
    2>/dev/null || true

sudo chmod -R 2775 \
    "$APP_DIR/storage" \
    "$APP_DIR/bootstrap/cache" \
    "$APP_DIR/database" \
    2>/dev/null || true

# O user pi também precisa de acesso
sudo usermod -aG www-data pi 2>/dev/null || true

ok "Permissões corrigidas"

# ── 6. Verificar assets compilados ───────────────────────────────────────────
hdr "6. Assets Frontend"

if [ -d "$APP_DIR/public/build" ] && [ -f "$APP_DIR/public/build/manifest.json" ]; then
    ASSET_COUNT=$(find "$APP_DIR/public/build" -name "*.js" -o -name "*.css" 2>/dev/null | wc -l)
    ok "Assets Vite: $ASSET_COUNT ficheiros"
else
    warn "Assets não compilados! public/build/ não existe ou está incompleto."
    info "O npm run build pode ter falhado no deploy. A tentar compilar..."
    if command -v npm &>/dev/null; then
        npm ci --prefer-offline --no-audit --no-fund --quiet 2>/dev/null || npm install --quiet 2>/dev/null || true
        npm run build 2>&1 | tail -5
        rm -rf node_modules 2>/dev/null || true
        ok "Assets compilados"
    else
        fail "npm não encontrado — instala Node.js no RPI3"
    fi
fi

# ── 7. Caches Laravel ─────────────────────────────────────────────────────────
hdr "7. Caches Laravel"

info "A limpar caches antigas..."
php artisan optimize:clear --quiet 2>/dev/null || true

info "A construir caches de produção..."
php artisan config:cache --quiet   && ok "config:cache ✓" || warn "config:cache falhou"
php artisan route:cache --quiet    && ok "route:cache ✓"  || warn "route:cache falhou"
php artisan view:cache --quiet     && ok "view:cache ✓"   || warn "view:cache falhou"
php artisan event:cache --quiet    && ok "event:cache ✓"  || warn "event:cache falhou"

# Storage link
php artisan storage:link --no-interaction 2>/dev/null || true
ok "storage:link ✓"

# ── 8. Corrigir post-receive hook ─────────────────────────────────────────────
hdr "8. Git Post-Receive Hook"

BARE_REPO="/home/pi/checkpraia.git"
HOOK_FILE="$BARE_REPO/hooks/post-receive"

if [ -f "$HOOK_FILE" ]; then
    # Verificar se o TARGET no hook está correto
    HOOK_TARGET=$(grep "^TARGET=" "$HOOK_FILE" 2>/dev/null | cut -d'"' -f2 || echo "")
    info "Hook TARGET atual: '${HOOK_TARGET:-não encontrado}'"

    if [ "$HOOK_TARGET" != "$APP_DIR" ]; then
        warn "Hook aponta para '$HOOK_TARGET' mas a app está em '$APP_DIR' — a corrigir..."
        # Reescrever o hook com o path correto
        cat > "$HOOK_FILE" << HOOK
#!/bin/bash
set -euo pipefail

TARGET="$APP_DIR"
GIT_DIR="$BARE_REPO"

echo ">>> Deploy iniciado: \$(date)"
cd "\$TARGET"
git --git-dir="\$GIT_DIR" --work-tree="\$TARGET" checkout -f main 2>&1 || \\
    git --work-tree="\$TARGET" checkout -f 2>&1

bash "\$TARGET/scripts/deploy.sh" "\$TARGET"

echo ">>> Deploy concluído!"
HOOK
        chmod +x "$HOOK_FILE"
        ok "Hook corrigido → TARGET=$APP_DIR"
    else
        ok "Hook OK → TARGET=$HOOK_TARGET"
    fi
else
    warn "Hook não encontrado em $HOOK_FILE"
fi

# ── 9. Recarregar serviços ────────────────────────────────────────────────────
hdr "9. Serviços"

# PHP-FPM
if sudo systemctl reload "php${PHP_VERSION}-fpm" 2>/dev/null; then
    ok "php${PHP_VERSION}-fpm: recarregado"
elif sudo systemctl restart "php${PHP_VERSION}-fpm" 2>/dev/null; then
    ok "php${PHP_VERSION}-fpm: reiniciado"
else
    warn "Não foi possível recarregar php-fpm"
fi

# Nginx — verificar config primeiro
if sudo nginx -t 2>/dev/null; then
    sudo systemctl reload nginx 2>/dev/null && ok "nginx: recarregado" || warn "nginx reload falhou"
else
    fail "nginx: configuração inválida!"
    sudo nginx -t 2>&1
fi

# Supervisor
sudo supervisorctl restart "checkpraia-worker:*" 2>/dev/null && ok "supervisor worker: reiniciado" || true

# ── 10. Verificar root do nginx ───────────────────────────────────────────────
hdr "10. Nginx root vs App path"

NGINX_ROOT=$(grep -r "root " /etc/nginx/sites-enabled/ 2>/dev/null | grep -v "#" | head -1 | awk '{print $NF}' | tr -d ';')
info "nginx root: $NGINX_ROOT"
info "app dir:    $APP_DIR/public"

if [ "$NGINX_ROOT" = "$APP_DIR/public" ]; then
    ok "Nginx root corresponde ao app dir"
else
    warn "NGINX ROOT ($NGINX_ROOT) ≠ APP_DIR/public ($APP_DIR/public)"
    warn "O nginx está configurado para um path diferente da app!"
    echo ""
    echo -e "  ${YELLOW}Solução:${NC}"
    echo "  1. Edita /etc/nginx/sites-available/checkpraia"
    echo "     Muda:  root $NGINX_ROOT"
    echo "     Para:  root $APP_DIR/public;"
    echo "  2. sudo nginx -t && sudo systemctl reload nginx"
    echo ""
    echo "  OU move a app para o path esperado pelo nginx:"
    echo "     sudo mv $APP_DIR $NGINX_ROOT/.."
fi

# ── 11. Teste final ───────────────────────────────────────────────────────────
hdr "11. Teste Final"

sleep 2

HTTP=$(curl -sk -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "err")
HTTPS=$(curl -sk -o /dev/null -w "%{http_code}" https://localhost 2>/dev/null || echo "err")

info "HTTP  localhost → $HTTP (esperado: 301)"
info "HTTPS localhost → $HTTPS (esperado: 200)"

if [ "$HTTPS" = "200" ] || [ "$HTTPS" = "301" ] || [ "$HTTPS" = "302" ]; then
    ok "Servidor a responder corretamente!"
elif [ "$HTTPS" = "403" ]; then
    fail "Ainda 403 — verifica nginx root e permissões acima"
elif [ "$HTTPS" = "500" ]; then
    fail "Ainda 500 — verifica logs:"
    echo "  tail -30 $APP_DIR/storage/logs/laravel.log"
else
    warn "Resposta: $HTTPS"
fi

# ── Resumo ────────────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}============================================${NC}"
echo -e "${GREEN}${BOLD}  Reparação concluída!                      ${NC}"
echo -e "${BOLD}============================================${NC}"
echo ""
echo -e "  ${CYAN}Logs da app:${NC}"
echo "    tail -f $APP_DIR/storage/logs/laravel.log"
echo ""
echo -e "  ${CYAN}Logs nginx:${NC}"
echo "    sudo tail -f /var/log/nginx/error.log"
echo ""
echo -e "  ${CYAN}Teste externo:${NC}"
PUBLIC_IP=$(curl -s4 --max-time 5 icanhazip.com 2>/dev/null || echo "?")
echo "    curl -Ik https://checkpraia.pt"
echo "    curl -Ik https://$PUBLIC_IP"
echo ""
