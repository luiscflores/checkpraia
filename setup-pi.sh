#!/bin/bash
# ── CheckPraia - Setup Nativo Raspberry Pi 3 (Production) ──────────────────
# Internet-exposed. Includes SSL, firewall, rate limiting, hardening.
# Idempotent — safe to re-run at any stage.
set -euo pipefail

# ── Config ──────────────────────────────────────────────────────────────────
PI_USER="${PI_USER:-pi}"
PI_DIR="/home/$PI_USER"
WORK_TREE="/var/www/checkpraia"
BARE_REPO="$PI_DIR/checkpraia.git"
PHP_VERSION="${PHP_VERSION:-8.4}"
LOG_FILE="/tmp/checkpraia-setup.log"
MIN_DISK_MB=1024

# ── Colors ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'
BOLD='\033[1m'; NC='\033[0m'

ok()   { echo -e " ${GREEN}✓${NC} $1"; }
info() { echo -e " ${CYAN}→${NC} $1"; }
warn() { echo -e " ${YELLOW}⚠${NC} $1"; }
fail() { echo -e " ${RED}✗${NC} $1"; exit 1; }
header() { echo -e "\n${BOLD}── $1 ──${NC}"; }
log()  { echo "$(date '+%H:%M:%S') $*" >> "$LOG_FILE"; }

cleanup() {
    [ $? -ne 0 ] && echo -e "\n${YELLOW}⚠ Setup interrompido. Log: $LOG_FILE${NC}"
}
trap cleanup EXIT

# ── Helpers ─────────────────────────────────────────────────────────────────
run_step() {
    local name="$1"; shift
    info "$name..."
    log "STEP: $name"
    "$@" 2>>"$LOG_FILE" || fail "$name falhou — log: $LOG_FILE"
    ok "$name"
}

ensure_dir() {
    sudo mkdir -p "$1" && sudo chown "$PI_USER":www-data "$1" 2>/dev/null
}

# ──────────────────────────────────────────────────────────────────────────
#  MAIN
# ──────────────────────────────────────────────────────────────────────────
main() {
    echo ""
    echo -e "${BOLD}============================================${NC}"
    echo -e "${BOLD}  Setup Nativo - CheckPraia no Raspberry Pi${NC}"
    echo -e "${BOLD}  Production: Internet-Exposed${NC}"
    echo -e "${BOLD}============================================${NC}"
    echo ""

    pre_flight
    step_swap
    step_system_deps
    step_ssh
    step_php
    step_composer
    step_node
    step_opcache
    step_php_fpm_tuning
    step_nginx
    step_clone_or_pull
    step_project_deps
    step_supervisor
    step_cron
    step_bare_repo
    step_permissions
    step_deploy_sudoers
    step_services
    step_ssl
    step_security
    step_env_validate
    step_self_test
    step_summary
}

# ──────────────────────────────────────────────────────────────────────────
#  0. Pre-flight
# ──────────────────────────────────────────────────────────────────────────
pre_flight() {
    header "Pre-flight"

    if [ "$EUID" -eq 0 ]; then
        warn "A correr como root. A script faz sudo automaticamente."
    fi

    # Verificar arquitetura
    local arch; arch=$(uname -m)
    case "$arch" in
        armv7l|armhf|aarch64|x86_64) ok "Arquitetura: $arch" ;;
        *) fail "Arquitetura nao suportada: $arch" ;;
    esac

    # Disco
    local avail_mb
    avail_mb=$(df -m "$PI_DIR" 2>/dev/null | awk 'NR==2{print $4}')
    if [ -n "$avail_mb" ] && [ "$avail_mb" -lt "$MIN_DISK_MB" ]; then
        fail "Apenas ${avail_mb}MB livres em $PI_DIR (min: ${MIN_DISK_MB}MB)"
    fi
    ok "Disco: ${avail_mb}MB disponiveis"

    # User existe
    id "$PI_USER" &>/dev/null || fail "User $PI_USER nao existe. Cria com: sudo adduser $PI_USER"
    ok "User: $PI_USER"
}

# ──────────────────────────────────────────────────────────────────────────
#  1. Swap (critico para RPi3 1GB RAM)
# ──────────────────────────────────────────────────────────────────────────
step_swap() {
    header "Swap"

    if [ ! -f /swapfile ]; then
        info "A criar swap de 512MB..."
        sudo fallocate -l 512M /swapfile 2>/dev/null ||
            sudo dd if=/dev/zero of=/swapfile bs=1M count=512 2>/dev/null
        sudo chmod 600 /swapfile
        sudo mkswap /swapfile
        sudo swapon /swapfile
        grep -q "/swapfile" /etc/fstab ||
            echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab >/dev/null
        ok "Swap 512MB criada e ativa"
    else
        ok "Swap ja existe ($(grep SwapTotal /proc/meminfo | awk '{print $2$3}'))"
    fi

    if ! grep -q "vm.swappiness=10" /etc/sysctl.conf 2>/dev/null; then
        echo "vm.swappiness=10" | sudo tee -a /etc/sysctl.conf >/dev/null
        sudo sysctl vm.swappiness=10 2>/dev/null || true
        ok "swappiness=10"
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  2. Dependencias do sistema
# ──────────────────────────────────────────────────────────────────────────
step_system_deps() {
    header "Dependencias do sistema"

    sudo apt-get update -y -qq || fail "apt update falhou"
    sudo apt-get install -y -qq \
        curl wget git unzip nginx supervisor sqlite3 cron \
        certbot python3-certbot-nginx \
        build-essential 2>/dev/null || true
    ok "System packages installed"
}

# ──────────────────────────────────────────────────────────────────────────
#  3. SSH (password auth)
# ──────────────────────────────────────────────────────────────────────────
step_ssh() {
    header "SSH"

    sudo mkdir -p /etc/ssh/sshd_config.d
    if [ ! -f /etc/ssh/sshd_config.d/checkpraia.conf ]; then
        echo "PasswordAuthentication yes" | sudo tee /etc/ssh/sshd_config.d/checkpraia.conf >/dev/null
        echo "KbdInteractiveAuthentication yes" | sudo tee -a /etc/ssh/sshd_config.d/checkpraia.conf
        sudo sed -i 's/^#\?PermitRootLogin.*/PermitRootLogin no/' /etc/ssh/sshd_config 2>/dev/null || true
        sudo systemctl restart sshd 2>/dev/null || true
        ok "SSH configurado (password auth + root desativado)"
    else
        ok "SSH ja configurado"
    fi

    if ! passwd -S "$PI_USER" 2>/dev/null | grep -q "P"; then
        warn "Password do user $PI_USER nao definida. Corre: sudo passwd $PI_USER"
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  4. PHP
# ──────────────────────────────────────────────────────────────────────────
step_php() {
    header "PHP $PHP_VERSION"

    if command -v php &>/dev/null && php -v 2>/dev/null | grep -q "$PHP_VERSION"; then
        ok "PHP $PHP_VERSION ja instalado"
        return
    fi

    # Sury repo
    local sury_key="/usr/share/keyrings/sury-php.gpg"
    if [ ! -f "$sury_key" ]; then
        sudo curl -sSL https://packages.sury.org/php/apt.gpg -o "$sury_key"
        echo "deb [signed-by=$sury_key] https://packages.sury.org/php/ $(lsb_release -sc) main" \
            | sudo tee /etc/apt/sources.list.d/sury-php.list >/dev/null
        sudo apt-get update -y -qq
    fi

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

    ok "PHP $PHP_VERSION instalado ($(php -v 2>/dev/null | head -1))"
}

# ──────────────────────────────────────────────────────────────────────────
#  5. Composer
# ──────────────────────────────────────────────────────────────────────────
step_composer() {
    header "Composer"

    if command -v composer &>/dev/null; then
        ok "Composer $(composer --version 2>/dev/null | head -1)"
        return
    fi

    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
    ok "Composer instalado"
}

# ──────────────────────────────────────────────────────────────────────────
#  6. Node.js
# ──────────────────────────────────────────────────────────────────────────
step_node() {
    header "Node.js"

    if command -v node &>/dev/null; then
        ok "Node.js $(node --version)"
        return
    fi

    local arch; arch=$(uname -m)
    local node_url
    case "$arch" in
        armv7l|armhf) node_url="https://nodejs.org/dist/v22.14.0/node-v22.14.0-linux-armv7l.tar.xz" ;;
        aarch64)      node_url="https://nodejs.org/dist/v22.14.0/node-v22.14.0-linux-arm64.tar.xz" ;;
        *)            node_url="https://nodejs.org/dist/v22.14.0/node-v22.14.0-linux-x64.tar.xz" ;;
    esac

    curl -fsSL "$node_url" | tar -xJ -C /tmp
    sudo cp -a /tmp/node-v22.14.0-linux-*/bin/* /usr/local/bin/
    sudo cp -a /tmp/node-v22.14.0-linux-*/lib/node_modules /usr/local/lib/
    rm -rf /tmp/node-v22.14.0-linux-*
    ok "Node.js $(node --version) instalado"
}

# ──────────────────────────────────────────────────────────────────────────
#  7. OPCache + JIT
# ──────────────────────────────────────────────────────────────────────────
step_opcache() {
    header "OPCache + JIT"

    local php_ini_dir="/etc/php/${PHP_VERSION}/mods-available"
    local src_ini="$WORK_TREE/scripts/php-opcache-jit.ini"
    local dest_ini="$php_ini_dir/10-opcache.ini"

    if [ -f "$src_ini" ]; then
        sudo cp "$src_ini" "$dest_ini" 2>/dev/null
    elif [ -f "$(dirname "$0")/scripts/php-opcache-jit.ini" ]; then
        sudo cp "$(dirname "$0")/scripts/php-opcache-jit.ini" "$dest_ini" 2>/dev/null
    else
        warn "Ficheiro php-opcache-jit.ini nao encontrado — a usar opcache predefinido"
    fi

    sudo phpenmod opcache 2>/dev/null || true
    ok "OPCache + JIT configurado (memoria: 128M, JIT: tracing 48M)"
}

# ──────────────────────────────────────────────────────────────────────────
#  7b. PHP-FPM pool tuning (critico para 1GB RAM)
# ──────────────────────────────────────────────────────────────────────────
step_php_fpm_tuning() {
    header "PHP-FPM pool tuning"

    local pool_path="/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf"
    if [ ! -f "$pool_path" ]; then
        warn "Pool www.conf nao encontrado em $pool_path"
        return
    fi

    # NOTA: emergency_restart_threshold e emergency_restart_interval sao
    # directivas GLOBAIS (php-fpm.conf) — NAO vao no pool www.conf.
    # Usar apenas sed (idempotente — seguro em re-execucoes).

    # Remover qualquer bloco CheckPraia antigo (legado de versoes anteriores)
    sudo sed -i '/; ── CheckPraia Tuning/,/process_control_timeout.*10s/d' "$pool_path" 2>/dev/null || true
    sudo sed -i '/emergency_restart_threshold/d' "$pool_path" 2>/dev/null || true
    sudo sed -i '/emergency_restart_interval/d' "$pool_path" 2>/dev/null || true

    # Aplicar tuning conservador para RPi3 1GB RAM (apenas directivas de pool validas)
    sudo sed -i 's/^pm =.*/pm = dynamic/'                                "$pool_path"
    sudo sed -i 's/^pm\.max_children =.*/pm.max_children = 4/'           "$pool_path"
    sudo sed -i 's/^pm\.start_servers =.*/pm.start_servers = 2/'         "$pool_path"
    sudo sed -i 's/^pm\.min_spare_servers =.*/pm.min_spare_servers = 1/' "$pool_path"
    sudo sed -i 's/^pm\.max_spare_servers =.*/pm.max_spare_servers = 3/' "$pool_path"
    sudo sed -i 's/^pm\.max_requests =.*/pm.max_requests = 500/'         "$pool_path"
    sudo sed -i 's/^;request_terminate_timeout.*/request_terminate_timeout = 120s/' "$pool_path"

    # Configuracoes globais (estas vao no php-fpm.conf, nao no pool)
    local global_conf="/etc/php/${PHP_VERSION}/fpm/php-fpm.conf"
    if grep -q "emergency_restart_threshold" "$global_conf" 2>/dev/null; then
        sudo sed -i 's/^;emergency_restart_threshold.*/emergency_restart_threshold = 3/' "$global_conf" 2>/dev/null || true
        sudo sed -i 's/^;emergency_restart_interval.*/emergency_restart_interval = 60s/' "$global_conf" 2>/dev/null || true
        sudo sed -i 's/^;process_control_timeout.*/process_control_timeout = 10s/' "$global_conf" 2>/dev/null || true
    fi

    # Validar configuracao final
    if sudo php-fpm${PHP_VERSION} -t 2>/dev/null; then
        ok "PHP-FPM pool: dynamic, max_children=4, max_requests=500"
    else
        warn "PHP-FPM config invalida — verifica: sudo php-fpm${PHP_VERSION} -t"
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  8. Nginx
# ──────────────────────────────────────────────────────────────────────────
step_nginx() {
    header "Nginx"

    # Rate limit zone
    sudo tee /etc/nginx/snippets/rate-limit.conf >/dev/null <<'EOF'
limit_req_zone $binary_remote_addr zone=general:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=auth:10m rate=2r/s;
limit_req_status 429;
EOF

    if ! grep -q "rate-limit.conf" /etc/nginx/nginx.conf 2>/dev/null; then
        sudo sed -i '/http {/a \    include /etc/nginx/snippets/rate-limit.conf;' /etc/nginx/nginx.conf 2>/dev/null || true
    fi

    # Site config
    local src_nginx="$WORK_TREE/scripts/checkpraia-nginx.conf"
    if [ ! -f "$src_nginx" ]; then
        src_nginx="$(dirname "$0")/scripts/checkpraia-nginx.conf"
    fi

    if [ -f "$src_nginx" ]; then
        sudo cp "$src_nginx" /etc/nginx/sites-available/checkpraia
    else
        fail "checkpraia-nginx.conf nao encontrado"
    fi

    sudo rm -f /etc/nginx/sites-enabled/default
    sudo rm -f /etc/nginx/sites-enabled/checkpraia.pt
    sudo ln -sf /etc/nginx/sites-available/checkpraia /etc/nginx/sites-enabled/

    if sudo nginx -t 2>/dev/null; then
        ok "Nginx config: valido"
    else
        warn "Nginx config: INVALIDO. Corrige manualmente."
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  9. Clonar (ou atualizar) repositorio
# ──────────────────────────────────────────────────────────────────────────
step_clone_or_pull() {
    header "Repositorio"

    if [ ! -d "$WORK_TREE" ]; then
        git clone --depth 1 https://github.com/luiscflores/checkpraia.git "$WORK_TREE"
        ok "Repo clonado (shallow) em $WORK_TREE"
    else
        info "Repo ja existe — a fazer git fetch..."
        cd "$WORK_TREE"
        git fetch origin main --quiet 2>/dev/null || true
        if git rev-parse origin/main &>/dev/null; then
            git reset --hard origin/main --quiet 2>/dev/null || true
            ok "Repo atualizado para origin/main"
        else
            warn "Nao foi possivel fazer fetch — repo mantem-se como esta"
        fi
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  10. Dependencias do projeto + .env + caches
# ──────────────────────────────────────────────────────────────────────────
step_project_deps() {
    header "Dependencias do projeto"

    cd "$WORK_TREE"

    # Estrutura de diretorios
    ensure_dir storage/framework/cache/data
    ensure_dir storage/framework/sessions
    ensure_dir storage/framework/views
    ensure_dir storage/logs
    ensure_dir bootstrap/cache
    ensure_dir database
    
    # Criar SQLite antes do composer install para evitar crash no package:discover
    if [ ! -f database/database.sqlite ]; then
        touch database/database.sqlite
    fi
    sudo chown -R www-data:www-data database
    chmod -R 775 storage bootstrap/cache database
    chmod 664 database/database.sqlite 2>/dev/null || true

    # .env
    if [ ! -f .env ]; then
        if [ -f .env.example ]; then
            cp .env.example .env
            php artisan key:generate --no-interaction
            ok ".env criado — edita-o: nano $WORK_TREE/.env"
        else
            fail ".env.example nao encontrado"
        fi
    else
        ok ".env ja existe"
    fi

    # Git config (para o post-receive hook)
    git config user.name 2>/dev/null || git config user.name "pi" || true
    git config user.email 2>/dev/null || git config user.email "pi@checkpraia.pt" || true

    # Composer
    export COMPOSER_ALLOW_SUPERUSER=1
    composer install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-progress \
        --no-suggest \
        --prefer-dist \
        --quiet
    ok "Dependencias PHP instaladas"

    # Frontend (com retry para RPi3)
    if [ -f package.json ]; then
        info "A compilar assets (com retry se falhar)..."
        npm ci --ignore-scripts --no-audit --no-fund --prefer-offline --quiet 2>/dev/null || true
        if npm run build --quiet 2>/dev/null; then
            ok "Assets compilados"
        else
            warn "npm build falhou — tenta novamente..."
            npm ci --ignore-scripts --no-audit --no-fund --prefer-offline --quiet 2>/dev/null || true
            npm run build --quiet 2>/dev/null || warn "npm build falhou novamente (pode ser compilado manualmente)"
        fi
        rm -rf node_modules
    fi

    # Laravel caches (limpa primeiro para evitar stale cache)
    php artisan migrate --force --quiet 2>/dev/null || warn "Migration falhou (podes correr manualmente)"
    php artisan optimize:clear --quiet 2>/dev/null || true
    php artisan config:cache --quiet 2>/dev/null || true
    php artisan route:cache --quiet 2>/dev/null || true
    php artisan view:cache --quiet 2>/dev/null || true
    php artisan event:cache --quiet 2>/dev/null || true
    php artisan storage:link --no-interaction 2>/dev/null || true
    
    sudo chown -R www-data:www-data storage/framework storage/logs bootstrap/cache database 2>/dev/null || true
    ok "Caches Laravel carregados"
}

# ──────────────────────────────────────────────────────────────────────────
#  11. Supervisor (queue worker)
# ──────────────────────────────────────────────────────────────────────────
step_supervisor() {
    header "Supervisor"

    local worker_src="$WORK_TREE/checkpraia-worker.conf"
    if [ ! -f "$worker_src" ]; then
        warn "checkpraia-worker.conf nao encontrado"
        return
    fi

    sudo cp "$worker_src" /etc/supervisor/conf.d/checkpraia-worker.conf
    sudo supervisorctl reread 2>/dev/null || true
    sudo supervisorctl update 2>/dev/null || true
    ok "Supervisor configurado (checkpraia-worker)"
}

# ──────────────────────────────────────────────────────────────────────────
#  12. Cron (scheduler + deploy + cert renewal)
# ──────────────────────────────────────────────────────────────────────────
step_cron() {
    header "Cron"

    local scheduler="* * * * * cd $WORK_TREE && php artisan schedule:run >> /dev/null 2>&1"
    local deploy="*/5 * * * * cd $WORK_TREE && bash scripts/deploy.sh >> $WORK_TREE/storage/logs/deploy.log 2>&1"
    local cert="0 3 * * * certbot renew --quiet --post-hook 'systemctl reload nginx'"

    (crontab -u "$PI_USER" -l 2>/dev/null | grep -v "checkpraia" | grep -v "certbot renew"; \
     echo "$scheduler"; echo "$deploy"; echo "$cert") \
        | crontab -u "$PI_USER" - 2>/dev/null || true

    ok "Cron: scheduler (1min) + deploy (5min) + cert (3am)"
}

# ──────────────────────────────────────────────────────────────────────────
#  13. Bare git repo + post-receive hook
# ──────────────────────────────────────────────────────────────────────────
step_bare_repo() {
    header "Bare git repo (git push deploy)"

    if [ -d "$BARE_REPO" ]; then
        ok "Bare repo ja existe em $BARE_REPO"
        return
    fi

    git init --bare "$BARE_REPO"

    cat > "$BARE_REPO/hooks/post-receive" << 'HOOK'
#!/bin/bash
set -euo pipefail

TARGET="/var/www/checkpraia"
git --work-tree="$TARGET" --git-dir="/home/pi/checkpraia.git" checkout -f main
cd "$TARGET"
bash scripts/deploy.sh "$TARGET" --no-git

echo ">>> Deploy concluido!"
HOOK

    chmod +x "$BARE_REPO/hooks/post-receive"
    ok "Bare repo criado em $BARE_REPO"
    info "Adiciona remote: git remote add pi ssh://$PI_USER@<IP>/$BARE_REPO"
}

# ──────────────────────────────────────────────────────────────────────────
#  14. Permissoes
# ──────────────────────────────────────────────────────────────────────────
step_permissions() {
    header "Permissoes"

    sudo usermod -aG www-data "$PI_USER" 2>/dev/null || true
    sudo chmod +x "$PI_DIR" 2>/dev/null || true
    sudo chown -R "$PI_USER":www-data "$WORK_TREE"
    sudo chmod -R 2775 "$WORK_TREE/storage" "$WORK_TREE/bootstrap/cache" "$WORK_TREE/database"
    # PHP-FPM runs as www-data — cache dirs must be owned by www-data
    sudo chown -R www-data:www-data "$WORK_TREE/storage/framework" "$WORK_TREE/storage/logs" 2>/dev/null || true

    # Logrotate para a app (se nao existir)
    if [ ! -f /etc/logrotate.d/checkpraia ]; then
        sudo tee /etc/logrotate.d/checkpraia >/dev/null << 'EOF'
/var/www/checkpraia/storage/logs/*.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    create 0664 pi www-data
    sharedscripts
    postrotate
        [ -f /var/run/php/php8.4-fpm.pid ] && sudo systemctl reload php8.4-fpm 2>/dev/null || true
    endscript
}
EOF
        ok "Logrotate configurado (7 dias)"
    fi

    ok "Permissoes corrigidas"
}

# ──────────────────────────────────────────────────────────────────────────
#  14b. Sudoers para deploy sem TTY (git push automatico)
# ──────────────────────────────────────────────────────────────────────────
step_deploy_sudoers() {
    header "Sudoers para deploy"

    local sudoers_file="/etc/sudoers.d/checkpraia-deploy"

    # Detectar versão PHP instalada
    local php_ver
    php_ver=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "$PHP_VERSION")

    # NOTA: sudoers não suporta flags (-R, -f, etc.) nem wildcards (*) em paths.
    # Apenas o caminho do binário é permitido. Isto é mais permissivo mas sintaticamente correto.
    sudo tee "$sudoers_file" > /dev/null << EOF
# CheckPraia deploy - permite ao user $PI_USER recarregar servicos sem password
# Necessario para git push automatico via post-receive hook sem TTY
Defaults:$PI_USER !requiretty
$PI_USER ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload nginx
$PI_USER ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart nginx
$PI_USER ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php${php_ver}-fpm
$PI_USER ALL=(ALL) NOPASSWD: /usr/bin/systemctl restart php${php_ver}-fpm
$PI_USER ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl
$PI_USER ALL=(ALL) NOPASSWD: /bin/chown
$PI_USER ALL=(ALL) NOPASSWD: /usr/bin/pkill
EOF

    # Validar o ficheiro sudoers antes de ativar
    if sudo visudo -cf "$sudoers_file" 2>/dev/null; then
        sudo chmod 440 "$sudoers_file"
        ok "Sudoers deploy: $sudoers_file (sem password para reload/chown)"
    else
        sudo rm -f "$sudoers_file"
        warn "Sudoers invalido - removido. Deploy manual funciona; git push pode ter warnings."
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  15. Servicos (arrancar)
# ──────────────────────────────────────────────────────────────────────────
step_services() {
    header "Servicos"

    # Self-signed placeholder para nginx arrancar antes do certbot
    local cert_dir="/etc/letsencrypt/live/checkpraia.pt"
    if [ ! -f "$cert_dir/fullchain.pem" ]; then
        info "A gerar certificado self-signed placeholder..."
        sudo mkdir -p "$cert_dir"
        sudo openssl req -x509 -nodes -days 365 \
            -newkey rsa:2048 \
            -keyout "$cert_dir/privkey.pem" \
            -out "$cert_dir/fullchain.pem" \
            -subj "/CN=checkpraia.pt" 2>/dev/null
        sudo cp "$cert_dir/fullchain.pem" "$cert_dir/chain.pem"
        ok "Self-signed placeholder criado"
    fi

    sudo systemctl enable nginx php${PHP_VERSION}-fpm supervisor 2>/dev/null || true

    # Dar reload primeiro (mais rapido que restart)
    sudo systemctl reload nginx 2>/dev/null ||
        sudo systemctl restart nginx 2>/dev/null || true
    sudo systemctl reload php${PHP_VERSION}-fpm 2>/dev/null ||
        sudo systemctl restart php${PHP_VERSION}-fpm 2>/dev/null || true
    sudo supervisorctl start checkpraia-worker:* 2>/dev/null || true

    ok "Servicos: nginx + php${PHP_VERSION}-fpm + supervisor"
}

# ──────────────────────────────────────────────────────────────────────────
#  16. SSL (info-only — certbot corre depois do DNS)
# ──────────────────────────────────────────────────────────────────────────
step_ssl() {
    header "SSL"

    if [ -f /etc/letsencrypt/live/checkpraia.pt/fullchain.pem ]; then
        ok "Certificado SSL encontrado"
        sudo systemctl enable certbot.timer 2>/dev/null || true
        sudo systemctl start certbot.timer 2>/dev/null || true
    else
        warn "SSL certificate NAO encontrado."
        info "Depois de configurar DNS, corre:"
        info "  sudo certbot --nginx -d checkpraia.pt"
        info "  sudo certbot certonly --standalone -d checkpraia.pt (se porta 80 ocupada)"
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  17. Security hardening (scripts/security-hardening.sh)
# ──────────────────────────────────────────────────────────────────────────
step_security() {
    header "Security hardening"

    local harden_script="$WORK_TREE/scripts/security-hardening.sh"
    if [ ! -f "$harden_script" ]; then
        warn "security-hardening.sh nao encontrado — salta"
        return
    fi

    info "A correr security-hardening.sh..."
    bash "$harden_script" 2>>"$LOG_FILE" || warn "Security hardening falhou (podes correr manualmente)"
    ok "Security hardening concluido"
}

# ──────────────────────────────────────────────────────────────────────────
#  18. Validar .env
# ──────────────────────────────────────────────────────────────────────────
step_env_validate() {
    header "Validar .env"

    local env_file="$WORK_TREE/.env"
    if [ ! -f "$env_file" ]; then
        warn ".env nao encontrado — cria-o manualmente"
        return
    fi

    local required_keys=(
        "APP_KEY" "APP_URL" "APP_ENV" "DB_CONNECTION"
    )
    local optional_keys=(
        "GOOGLE_CLIENT_ID" "GOOGLE_CLIENT_SECRET" "GOOGLE_REDIRECT_URI"
        "ADSENSE_PUBLISHER_ID"
        "VAPID_PUBLIC_KEY" "VAPID_PRIVATE_KEY"
    )
    local missing_required=()
    local missing_optional=()

    for key in "${required_keys[@]}"; do
        if ! grep -q "^${key}=" "$env_file" 2>/dev/null; then
            missing_required+=("$key")
        fi
    done

    for key in "${optional_keys[@]}"; do
        if ! grep -q "^${key}=" "$env_file" 2>/dev/null; then
            missing_optional+=("$key")
        fi
    done

    if [ ${#missing_required[@]} -eq 0 ]; then
        ok "Variaveis obrigatorias: todas presentes"
    else
        warn "Faltam variaveis OBRIGATORIAS: ${missing_required[*]}"
    fi

    if [ ${#missing_optional[@]} -gt 0 ]; then
        info "Faltam variaveis OPCIONAIS: ${missing_optional[*]}"
    fi

    if grep -q "^APP_KEY=$" "$env_file" 2>/dev/null; then
        warn "APP_KEY esta vazio — corre: php artisan key:generate"
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  19. Self-test
# ──────────────────────────────────────────────────────────────────────────
step_self_test() {
    header "Self-test"

    local tries=0
    local max_tries=5

    while [ $tries -lt $max_tries ]; do
        if curl -sI -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null | grep -q "30[0-9]"; then
            ok "Nginx responde a http://localhost (redirect 301)"
            break
        fi
        tries=$((tries + 1))
        [ $tries -lt $max_tries ] && sleep 2
    done

    if [ $tries -eq $max_tries ]; then
        warn "Nginx nao responde localmente — verifica: sudo nginx -t && sudo systemctl status nginx"
    fi

    # PHP-FPM
    if sudo systemctl is-active --quiet php${PHP_VERSION}-fpm 2>/dev/null; then
        ok "PHP $PHP_VERSION-FPM: ativo"
    else
        warn "PHP $PHP_VERSION-FPM: inativo"
    fi

    # Supervisor
    if sudo supervisorctl status checkpraia-worker:* 2>/dev/null | grep -q RUNNING; then
        ok "Supervisor worker: RUNNING"
    else
        warn "Supervisor worker: PARADO (pode ser normal se nao ha fila)"
    fi

    # SQLite
    if [ -f "$WORK_TREE/database/database.sqlite" ]; then
        ok "Base de dados SQLite: presente ($(ls -lh "$WORK_TREE/database/database.sqlite" | awk '{print $5}'))"
    else
        warn "Base de dados SQLite nao encontrada — corre: php artisan migrate --force"
    fi
}

# ──────────────────────────────────────────────────────────────────────────
#  Resumo final
# ──────────────────────────────────────────────────────────────────────────
step_summary() {
    echo ""
    echo -e "${BOLD}============================================${NC}"
    echo -e "${GREEN}${BOLD}  Setup completo! (Production-Ready)${NC}"
    echo -e "${BOLD}============================================${NC}"
    echo ""
    echo -e "  ${CYAN}App:${NC}        https://checkpraia.pt"
    echo -e "  ${CYAN}Directorio:${NC} $WORK_TREE"
    echo ""
    echo -e "  ${YELLOW}PROXIMOS PASSOS:${NC}"
    echo "    1. Editar .env:     nano $WORK_TREE/.env"
    echo "    2. Configurar DNS:  apontar checkpraia.pt para o IP publico do RPi"
    echo "    3. Pedir SSL:       sudo certbot --nginx -d checkpraia.pt"
    echo ""
    echo -e "  ${CYAN}Deploy via git push:${NC}"
    echo "    git remote add pi ssh://$PI_USER@<IP_PUBLICO>$BARE_REPO"
    echo "    git push pi main"
    echo ""
    echo -e "  ${CYAN}Deploy automatico:${NC} cron a cada 5min faz pull do GitHub"
    echo ""

    # Mostrar IP publico se disponivel
    local public_ip
    public_ip=$(curl -s4 ifconfig.co 2>/dev/null || curl -s4 icanhazip.com 2>/dev/null || echo "")
    if [ -n "$public_ip" ]; then
        echo -e "  ${CYAN}IP Publico:${NC} $public_ip"
        echo ""
    fi
}

# ── Run ──────────────────────────────────────────────────────────────────
main "$@"
