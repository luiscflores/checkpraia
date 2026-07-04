#!/bin/bash
set -euo pipefail

PI_USER="${PI_USER:-pi}"
PI_DIR="/home/$PI_USER"
REPO_NAME="checkpraia.git"
WORK_TREE="$PI_DIR/checkpraia"
BARE_REPO="$PI_DIR/$REPO_NAME"

echo "=== 1. Instalar Docker ==="
if ! command -v docker &>/dev/null; then
    curl -fsSL https://get.docker.com | sh
    sudo usermod -aG docker "$PI_USER"
    echo "Docker installed. Log out and back in for group changes to take effect."
else
    echo "Docker already installed."
fi

echo "=== 2. Criar bare git repo ==="
mkdir -p "$BARE_REPO"
git init --bare "$BARE_REPO"

echo "=== 3. Criar working directory ==="
mkdir -p "$WORK_TREE"

echo "=== 4. Configurar post-receive hook ==="
cat > "$BARE_REPO/hooks/post-receive" << 'HOOK'
#!/bin/bash
set -euo pipefail

TARGET="/home/pi/checkpraia"
mkdir -p "$TARGET"
git --work-tree="$TARGET" checkout -f

cd "$TARGET"

# Garantir que .env existe (criado a partir de .env.example na primeira vez)
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        # Gerar APP_KEY
        APP_KEY="base64:$(openssl rand -base64 32)"
        if grep -q "^APP_KEY=" .env; then
            sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
        else
            echo "APP_KEY=${APP_KEY}" >> .env
        fi
        echo ">>> .env criado a partir de .env.example com APP_KEY gerada."
        echo ">>> Edita .env no Pi para adicionares chaves de API (VAPID, Google, etc.)"
    else
        echo "ERRO: .env.example não encontrado!"
        exit 1
    fi
fi

mkdir -p storage bootstrap/cache

# Construir e fazer deploy
docker compose up -d --build

echo ">>> Deploy concluído!"
HOOK

chmod +x "$BARE_REPO/hooks/post-receive"

echo ""
echo "============================================"
echo "  Setup completo no Raspberry Pi!"
echo "============================================"
echo ""
echo "No teu computador, executa:"
echo ""
echo "  1. git remote add pi ssh://$PI_USER@<IP_DO_PI>$BARE_REPO"
echo "  2. git push pi main"
echo ""
echo "Depois do primeiro push, edita o .env no Pi para pores as chaves:"
echo "  ssh $PI_USER@<IP_DO_PI> 'nano $WORK_TREE/.env'"
echo "  ssh $PI_USER@<IP_DO_PI> 'cd $WORK_TREE && docker compose up -d --build'"
