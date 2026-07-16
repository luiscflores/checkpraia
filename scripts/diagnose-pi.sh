#!/bin/bash
# ── CheckPraia - Diagnóstico e Reparação Rápida (RPI3 Production) ──────────
# Corre no RPI3 via SSH para diagnosticar erro 403/500 do nginx
# Uso: bash scripts/diagnose-pi.sh [--fix]
# --fix: tenta corrigir automaticamente os problemas encontrados

set -uo pipefail

APP_DIR="${APP_DIR:-/home/pi/checkpraia}"
PHP_VERSION="${PHP_VERSION:-8.4}"
FIX="${1:-}"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'
BOLD='\033[1m'; NC='\033[0m'

ok()   { echo -e " ${GREEN}✓${NC} $1"; }
fail() { echo -e " ${RED}✗${NC} $1"; ISSUES=$((ISSUES+1)); }
warn() { echo -e " ${YELLOW}⚠${NC} $1"; WARNINGS=$((WARNINGS+1)); }
info() { echo -e " ${CYAN}→${NC} $1"; }
hdr()  { echo -e "\n${BOLD}── $1 ──${NC}"; }

ISSUES=0
WARNINGS=0

echo ""
echo -e "${BOLD}============================================${NC}"
echo -e "${BOLD}  CheckPraia - Diagnóstico RPI3 Production ${NC}"
echo -e "${BOLD}  $(date '+%Y-%m-%d %H:%M:%S')            ${NC}"
echo -e "${BOLD}============================================${NC}"
echo ""

# ── 1. Serviços ──────────────────────────────────────────────────────────────
hdr "1. Serviços"

check_service() {
    local svc="$1"
    if systemctl is-active --quiet "$svc" 2>/dev/null; then
        ok "$svc: ATIVO"
    else
        fail "$svc: INATIVO"
        if [ "$FIX" = "--fix" ]; then
            info "A reiniciar $svc..."
            sudo systemctl restart "$svc" 2>/dev/null && ok "$svc reiniciado" || warn "Falhou ao reiniciar $svc"
        fi
    fi
}

check_service nginx
check_service "php${PHP_VERSION}-fpm"
check_service supervisor

# ── 2. Nginx ─────────────────────────────────────────────────────────────────
hdr "2. Nginx"

if sudo nginx -t 2>/dev/null; then
    ok "nginx -t: configuração VÁLIDA"
else
    fail "nginx -t: configuração INVÁLIDA"
    sudo nginx -t 2>&1 | head -20
fi

# Verificar se o socket PHP-FPM existe
FPM_SOCK="/var/run/php/php${PHP_VERSION}-fpm.sock"
if [ -S "$FPM_SOCK" ]; then
    ok "Socket PHP-FPM: $FPM_SOCK existe"
else
    fail "Socket PHP-FPM: $FPM_SOCK NÃO EXISTE"
    info "Sockets existentes:"
    ls /var/run/php/ 2>/dev/null || echo "  (diretório vazio ou não existe)"
    if [ "$FIX" = "--fix" ]; then
        info "A tentar reiniciar PHP-FPM..."
        sudo systemctl restart "php${PHP_VERSION}-fpm" 2>/dev/null && ok "PHP-FPM reiniciado" || warn "Falhou"
    fi
fi

# Verificar que nginx está realmente a ouvir nas portas
info "Portas abertas:"
ss -tlnp 2>/dev/null | grep -E ':(80|443)\s' | while read -r line; do
    echo "  $line"
done

# Verificar site enabled
if [ -L /etc/nginx/sites-enabled/checkpraia ]; then
    ok "Site checkpraia: enabled"
elif [ -f /etc/nginx/sites-enabled/checkpraia ]; then
    ok "Site checkpraia: enabled (ficheiro)"
else
    fail "Site checkpraia: NÃO está enabled"
    info "Sites enabled:"
    ls /etc/nginx/sites-enabled/ 2>/dev/null
    if [ "$FIX" = "--fix" ]; then
        sudo ln -sf /etc/nginx/sites-available/checkpraia /etc/nginx/sites-enabled/ 2>/dev/null && ok "Symlink criado" || warn "Falhou"
    fi
fi

# ── 3. SSL / Certificados ────────────────────────────────────────────────────
hdr "3. SSL / Certificados"

CERT="/etc/letsencrypt/live/checkpraia.pt/fullchain.pem"
KEY="/etc/letsencrypt/live/checkpraia.pt/privkey.pem"
CHAIN="/etc/letsencrypt/live/checkpraia.pt/chain.pem"

if [ -f "$CERT" ]; then
    # Verificar se é self-signed ou real
    ISSUER=$(openssl x509 -in "$CERT" -noout -issuer 2>/dev/null | grep -o "O=[^/]*" | head -1)
    EXPIRY=$(openssl x509 -in "$CERT" -noout -enddate 2>/dev/null | cut -d= -f2)
    if echo "$ISSUER" | grep -qi "Let.s Encrypt"; then
        ok "Certificado: Let's Encrypt válido (expira: $EXPIRY)"
    else
        warn "Certificado: Self-signed / não é Let's Encrypt (issuer: $ISSUER)"
        info "Expira: $EXPIRY"
        info "Para obter SSL real: sudo certbot --nginx -d checkpraia.pt -d www.checkpraia.pt"
    fi
else
    fail "Certificado SSL não encontrado em $CERT"
fi

[ -f "$KEY" ]   && ok "Chave privada: presente" || fail "Chave privada: AUSENTE"
[ -f "$CHAIN" ] && ok "Chain: presente"         || fail "Chain: AUSENTE"

# ── 4. Diretório da app ───────────────────────────────────────────────────────
hdr "4. Diretório da App"

if [ -d "$APP_DIR" ]; then
    ok "Diretório: $APP_DIR"
else
    fail "Diretório da app não encontrado: $APP_DIR"
fi

if [ -f "$APP_DIR/public/index.php" ]; then
    ok "public/index.php: presente"
else
    fail "public/index.php: AUSENTE (a app não está deployada!)"
fi

if [ -f "$APP_DIR/.env" ]; then
    ok ".env: presente"
    # Verificar APP_KEY
    APP_KEY=$(grep "^APP_KEY=" "$APP_DIR/.env" | cut -d= -f2-)
    if [ -z "$APP_KEY" ]; then
        fail "APP_KEY está VAZIO no .env"
        if [ "$FIX" = "--fix" ]; then
            cd "$APP_DIR" && php artisan key:generate --no-interaction 2>/dev/null && ok "APP_KEY gerado" || warn "Falhou"
        fi
    else
        ok "APP_KEY: definido"
    fi
    # APP_ENV
    APP_ENV=$(grep "^APP_ENV=" "$APP_DIR/.env" | cut -d= -f2-)
    info "APP_ENV=$APP_ENV"
    # APP_URL
    APP_URL=$(grep "^APP_URL=" "$APP_DIR/.env" | cut -d= -f2-)
    info "APP_URL=$APP_URL"
else
    fail ".env: AUSENTE"
    if [ "$FIX" = "--fix" ] && [ -f "$APP_DIR/.env.example" ]; then
        cp "$APP_DIR/.env.example" "$APP_DIR/.env"
        cd "$APP_DIR" && php artisan key:generate --no-interaction 2>/dev/null
        ok ".env criado (edita-o antes de continuar)"
    fi
fi

# ── 5. Permissões ────────────────────────────────────────────────────────────
hdr "5. Permissões"

check_writable() {
    local dir="$APP_DIR/$1"
    if [ -d "$dir" ]; then
        local owner; owner=$(stat -c '%U:%G' "$dir" 2>/dev/null)
        local perms; perms=$(stat -c '%a' "$dir" 2>/dev/null)
        if [ -w "$dir" ] || sudo -u www-data test -w "$dir" 2>/dev/null; then
            ok "$1: writable ($owner, $perms)"
        else
            fail "$1: NÃO writable ($owner, $perms)"
            if [ "$FIX" = "--fix" ]; then
                sudo chmod -R 2775 "$dir" 2>/dev/null || true
                sudo chown -R www-data:www-data "$dir" 2>/dev/null || true
                ok "Permissões corrigidas em $1"
            fi
        fi
    else
        fail "$1: diretório NÃO EXISTE"
        if [ "$FIX" = "--fix" ]; then
            sudo mkdir -p "$dir"
            sudo chown www-data:www-data "$dir"
            sudo chmod 2775 "$dir"
            ok "$1 criado"
        fi
    fi
}

check_writable "storage"
check_writable "storage/logs"
check_writable "storage/framework"
check_writable "storage/framework/cache"
check_writable "storage/framework/sessions"
check_writable "storage/framework/views"
check_writable "bootstrap/cache"
check_writable "database"

# Verificar SQLite
if [ -f "$APP_DIR/database/database.sqlite" ]; then
    local_size=$(ls -lh "$APP_DIR/database/database.sqlite" 2>/dev/null | awk '{print $5}')
    ok "SQLite: $local_size"
    # Verificar que www-data consegue ler
    if sudo -u www-data test -r "$APP_DIR/database/database.sqlite" 2>/dev/null; then
        ok "SQLite: legível por www-data"
    else
        fail "SQLite: não legível por www-data"
        if [ "$FIX" = "--fix" ]; then
            sudo chown www-data:www-data "$APP_DIR/database/database.sqlite"
            sudo chmod 664 "$APP_DIR/database/database.sqlite"
            ok "Permissões SQLite corrigidas"
        fi
    fi
else
    warn "SQLite não encontrado — a correr migrations..."
    if [ "$FIX" = "--fix" ]; then
        cd "$APP_DIR" && php artisan migrate --force 2>&1 | tail -5
    fi
fi

# ── 6. Assets compilados ──────────────────────────────────────────────────────
hdr "6. Assets Frontend (Vite)"

if [ -d "$APP_DIR/public/build" ]; then
    ASSET_COUNT=$(find "$APP_DIR/public/build" -name "*.js" -o -name "*.css" 2>/dev/null | wc -l)
    ok "public/build/: $ASSET_COUNT assets"
else
    fail "public/build/: NÃO EXISTE — assets não compilados!"
    info "Para compilar: cd $APP_DIR && npm ci && npm run build"
fi

if [ -f "$APP_DIR/public/build/manifest.json" ]; then
    ok "manifest.json: presente"
else
    warn "manifest.json: ausente em public/build/"
fi

# ── 7. Laravel caches ────────────────────────────────────────────────────────
hdr "7. Laravel Caches"

check_cache() {
    local file="$APP_DIR/bootstrap/cache/$1"
    if [ -f "$file" ]; then
        ok "$1: existe"
    else
        warn "$1: não existe (não é crítico, mas pode afetar performance)"
    fi
}

check_cache "config.php"
check_cache "routes-v7.php"

# ── 8. Logs recentes ──────────────────────────────────────────────────────────
hdr "8. Logs Recentes"

info "Últimas linhas do laravel.log:"
if [ -f "$APP_DIR/storage/logs/laravel.log" ]; then
    tail -30 "$APP_DIR/storage/logs/laravel.log" | grep -E "ERROR|CRITICAL|Exception|403|500" | tail -10 || echo "  (sem erros recentes)"
else
    warn "laravel.log não encontrado"
fi

echo ""
info "Últimas linhas do nginx error.log:"
sudo tail -20 /var/log/nginx/error.log 2>/dev/null | tail -10 || warn "Sem acesso a nginx error.log"

echo ""
info "Últimas linhas do nginx access.log (respostas 4xx/5xx):"
sudo tail -100 /var/log/nginx/access.log 2>/dev/null | grep -E '" [45][0-9]{2} ' | tail -10 || echo "  (sem erros recentes)"

# ── 9. Recursos do sistema ───────────────────────────────────────────────────
hdr "9. Recursos do Sistema"

info "Memória:"
free -h 2>/dev/null | head -3

echo ""
info "Disco:"
df -h "$APP_DIR" 2>/dev/null | head -2

echo ""
info "CPU (load):"
uptime

echo ""
info "PHP-FPM workers:"
ps aux 2>/dev/null | grep php-fpm | grep -v grep | wc -l | xargs echo "  Processos PHP-FPM:"

# ── 10. Teste HTTP local ──────────────────────────────────────────────────────
hdr "10. Teste HTTP Local"

HTTP_CODE=$(curl -sk -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "falhou")
if [ "$HTTP_CODE" = "301" ] || [ "$HTTP_CODE" = "302" ]; then
    ok "HTTP localhost → redirect $HTTP_CODE (correto: redireciona para HTTPS)"
elif [ "$HTTP_CODE" = "200" ]; then
    ok "HTTP localhost → 200 OK"
elif [ "$HTTP_CODE" = "000" ] || [ "$HTTP_CODE" = "falhou" ]; then
    fail "HTTP localhost → sem resposta (nginx não está a servir)"
else
    warn "HTTP localhost → $HTTP_CODE (inesperado)"
fi

HTTPS_CODE=$(curl -sk -o /dev/null -w "%{http_code}" https://localhost 2>/dev/null || echo "falhou")
if [ "$HTTPS_CODE" = "200" ]; then
    ok "HTTPS localhost → 200 OK"
elif [ "$HTTPS_CODE" = "301" ] || [ "$HTTPS_CODE" = "302" ]; then
    ok "HTTPS localhost → $HTTPS_CODE"
elif [ "$HTTPS_CODE" = "403" ]; then
    fail "HTTPS localhost → 403 FORBIDDEN ← PROBLEMA ENCONTRADO"
    info "Possíveis causas: permissões, root errado, php-fpm parado"
elif [ "$HTTPS_CODE" = "500" ]; then
    fail "HTTPS localhost → 500 INTERNAL SERVER ERROR ← PROBLEMA ENCONTRADO"
    info "Verifica: tail -50 $APP_DIR/storage/logs/laravel.log"
else
    warn "HTTPS localhost → $HTTPS_CODE"
fi

# ── 11. Connectivity (IP público) ────────────────────────────────────────────
hdr "11. IP Público"
PUBLIC_IP=$(curl -s4 --max-time 5 icanhazip.com 2>/dev/null || curl -s4 --max-time 5 ifconfig.co 2>/dev/null || echo "não obtido")
info "IP Público: $PUBLIC_IP"

# ── Resumo ────────────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}============================================${NC}"
if [ "$ISSUES" -eq 0 ] && [ "$WARNINGS" -eq 0 ]; then
    echo -e "${GREEN}${BOLD}  Tudo OK! Sem problemas encontrados.${NC}"
elif [ "$ISSUES" -eq 0 ]; then
    echo -e "${YELLOW}${BOLD}  $WARNINGS avisos encontrados (sem erros críticos).${NC}"
else
    echo -e "${RED}${BOLD}  $ISSUES ERROS + $WARNINGS avisos encontrados.${NC}"
    echo ""
    echo -e "  ${CYAN}Para tentar corrigir automaticamente:${NC}"
    echo "    bash scripts/diagnose-pi.sh --fix"
fi
echo -e "${BOLD}============================================${NC}"
echo ""

if [ "$ISSUES" -gt 0 ] && [ "$FIX" = "--fix" ]; then
    hdr "A aplicar correções finais..."
    cd "$APP_DIR" 2>/dev/null || true

    info "A limpar e reconstruir caches Laravel..."
    php artisan optimize:clear --quiet 2>/dev/null || true
    php artisan config:cache --quiet 2>/dev/null || true
    php artisan route:cache --quiet 2>/dev/null || true
    php artisan view:cache --quiet 2>/dev/null || true

    info "A recarregar nginx e PHP-FPM..."
    sudo systemctl reload nginx 2>/dev/null || sudo systemctl restart nginx 2>/dev/null || true
    sudo systemctl reload "php${PHP_VERSION}-fpm" 2>/dev/null || sudo systemctl restart "php${PHP_VERSION}-fpm" 2>/dev/null || true

    ok "Correções aplicadas! Testa novamente: curl -Ik https://checkpraia.pt"
fi
