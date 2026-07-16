#!/bin/bash
# ── CheckPraia - Reinstalação Total Raspberry Pi 3 ──────────────────────────
# Corre este script no teu RPi3 para limpar os erros e configurar o servidor
# de raiz com as melhores práticas.
set -euo pipefail

echo ">>> 🚀 A iniciar o Reset Total do CheckPraia no RPi3..."

WWW_DIR="/var/www/checkpraia"
GIT_REPO="/home/pi/checkpraia.git"

# 1. Apagar diretoria baralhada /home/pi/checkpraia se existir
if [ -d "/home/pi/checkpraia" ] && [ "/home/pi/checkpraia" != "$GIT_REPO" ]; then
    echo ">>> A limpar a diretoria de testes velha em /home/pi/checkpraia..."
    sudo rm -rf "/home/pi/checkpraia"
fi

# 2. Configurar permissões de sudoer sem password para o deploy automático
echo ">>> A configurar permissões de Deploy para o Git Hook..."
echo "pi ALL=(ALL) NOPASSWD: /bin/chown, /bin/chmod, /bin/systemctl, /usr/bin/systemctl, /usr/bin/php, /bin/rm, /usr/bin/rm" | sudo tee /etc/sudoers.d/checkpraia-deploy > /dev/null

# 3. Criar Pasta de Produção
echo ">>> A criar pasta de produção em $WWW_DIR..."
sudo mkdir -p "$WWW_DIR"
sudo chown -R www-data:www-data "$WWW_DIR"
sudo chmod -R 775 "$WWW_DIR"
sudo usermod -a -G www-data pi

# 4. Limpar e criar o Git Repo
echo ">>> A preparar o Repositório Git..."
sudo rm -rf "$GIT_REPO"
git init --bare "$GIT_REPO"

# 5. Criar o super Hook de Deploy (Post-Receive)
echo ">>> A injetar o novo pipeline de CI/CD..."
cat << 'EOF' > "$GIT_REPO/hooks/post-receive"
#!/bin/bash
set -euo pipefail

echo ">>> 🚀 A receber o novo código CheckPraia..."

WWW_DIR="/var/www/checkpraia"

# Descarregar o código DIRETAMENTE do push (sem ir ao Github!)
git --work-tree="$WWW_DIR" --git-dir="/home/pi/checkpraia.git" checkout -f main

cd "$WWW_DIR"

echo ">>> A instalar dependências..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction --quiet

if [ -f package.json ]; then
    echo ">>> A compilar Frontend..."
    npm ci --no-audit --no-fund --quiet || npm install --no-audit --no-fund --quiet
    npm run build --quiet
    rm -rf node_modules
fi

echo ">>> A preparar Base de Dados..."
mkdir -p database bootstrap/cache storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

# Forçar permissões no SQLite para www-data usar o ficheiro e criar logs de WAL sem erros
sudo chown -R www-data:www-data "$WWW_DIR"
sudo chmod -R 775 "$WWW_DIR"

DB_SIZE=$(stat -c%s "database/database.sqlite")
if [ "$DB_SIZE" -lt 10240 ]; then
    echo ">>> Base de dados vazia! A executar Migrate + Seed inicial..."
    sudo -u www-data php artisan migrate:fresh --seed --force --quiet
else
    echo ">>> A executar migrações standard..."
    sudo -u www-data php artisan migrate --force --quiet
fi

echo ">>> A otimizar caches (JIT/OPcache prep)..."
sudo rm -f bootstrap/cache/*.php
sudo -u www-data php artisan optimize:clear --quiet
sudo -u www-data php artisan config:cache --quiet
sudo -u www-data php artisan route:cache --quiet
sudo -u www-data php artisan view:cache --quiet

echo ">>> A reiniciar serviços..."
_PHP_FPM=$(systemctl list-units --type=service --state=active 2>/dev/null | grep 'php.*fpm' | awk '{print $1}' | head -1)
sudo systemctl reload "$_PHP_FPM" || true
sudo systemctl reload nginx || true

echo ">>> ✅ Deploy 100% Concluído com Sucesso!"
EOF

chmod +x "$GIT_REPO/hooks/post-receive"

echo ">>> ✅ Script de Setup Concluído."
echo ">>> O teu RPi3 está 100% pronto e limpo!"
