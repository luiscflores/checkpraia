#!/bin/bash
# Correr este script no Raspberry Pi para limpar tudo do Docker
set -euo pipefail

cd /home/pi/checkpraia 2>/dev/null || true

echo "=== 1. Parar container checkpraia ==="
docker stop checkpraia-app-1 2>/dev/null || echo "  (container ja parado ou inexistente)"

echo "=== 2. Remover container ==="
docker rm checkpraia-app-1 2>/dev/null || echo "  (ja removido)"

echo "=== 3. Remover volumes ==="
docker volume rm checkpraia_checkpraia_storage checkpraia_checkpraia_database 2>/dev/null || echo "  (volumes inexistentes)"

echo "=== 4. Remover imagem ==="
docker rmi checkpraia-app 2>/dev/null || docker image prune -f --filter label=com.docker.compose.project=checkpraia 2>/dev/null || echo "  (imagem inexistente)"

echo "=== 5. Limpar recursos nao usados ==="
docker system prune -f --volumes 2>/dev/null || true

echo ""
echo "=== Docker cleanup concluido! ==="
echo ""
echo "Se quiseres remover completamente o Docker do sistema:"
echo "  sudo apt-get purge -y docker-ce docker-ce-cli docker-buildx-plugin docker-compose-plugin"
echo "  sudo rm -rf /var/lib/docker"
echo ""
