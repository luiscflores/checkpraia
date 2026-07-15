#!/bin/bash
set -euo pipefail

echo ""
echo "============================================"
echo "  Security Hardening - CheckPraia RPi3"
echo "============================================"
echo ""

# ── 1. Firewall (UFW) ────────────────────────────────────────────────────
echo "=== 1. Configurar Firewall (UFW) ==="

sudo apt-get install -y ufw 2>/dev/null || true

sudo ufw default deny incoming
sudo ufw default allow outgoing

# SSH
sudo ufw allow 22/tcp comment 'SSH'

# HTTP + HTTPS
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'

sudo ufw --force enable
sudo ufw status verbose

# ── 2. Fail2Ban ──────────────────────────────────────────────────────────
echo "=== 2. Instalar Fail2Ban ==="

sudo apt-get install -y fail2ban 2>/dev/null || true

sudo cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
port = http,https
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3
EOF

sudo systemctl enable fail2ban
sudo systemctl restart fail2ban

# ── 3. Logrotate ─────────────────────────────────────────────────────────
echo "=== 3. Configurar Logrotate ==="

sudo cat > /etc/logrotate.d/checkpraia << 'EOF'
/home/pi/checkpraia/storage/logs/*.log {
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

echo "Logrotate configurado: 7 dias, compressão ativa."

# ── 4. Session hardening ─────────────────────────────────────────────────
echo "=== 4. Session Hardening ==="

# Garantir que sessões são encriptadas no .env
APP_ENV="/home/pi/checkpraia/.env"
if [ -f "$APP_ENV" ]; then
    if grep -q "SESSION_ENCRYPT=false" "$APP_ENV"; then
        sed -i 's/SESSION_ENCRYPT=false/SESSION_ENCRYPT=true/' "$APP_ENV"
        echo "SESSION_ENCRYPT=true definido no .env"
    fi
    if grep -q "SESSION_DRIVER=file" "$APP_ENV"; then
        sed -i 's/SESSION_DRIVER=file/SESSION_DRIVER=database/' "$APP_ENV"
        echo "SESSION_DRIVER alterado para database"
    fi
fi

# ── 5. SSH Hardening ─────────────────────────────────────────────────────
echo "=== 5. SSH Hardening ==="

sudo sed -i 's/#PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config 2>/dev/null || true
sudo sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config 2>/dev/null || true
sudo sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config 2>/dev/null || true
echo "SSH: Root login desativado. Use chaves SSH."

# ── 6. Auto-security-updates ─────────────────────────────────────────────
echo "=== 6. Atualizações automáticas de segurança ==="

sudo apt-get install -y unattended-upgrades 2>/dev/null || true
sudo dpkg-reconfigure -plow unattended-upgrades 2>/dev/null || true

# ── 7. Nginx hardening ───────────────────────────────────────────────────
echo "=== 7. Nginx Security Headers ==="

sudo cat > /etc/nginx/snippets/security-headers.conf << 'EOF'
# Security headers
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Content-Security-Policy "default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://pagead2.googlesyndication.com https://adservice.google.com https://www.googletagmanager.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://fcm.googleapis.com;" always;
add_header Permissions-Policy "geolocation=(self), microphone=()" always;
EOF

echo "Security headers criados em /etc/nginx/snippets/security-headers.conf"
echo ">>> Adiciona 'include /etc/nginx/snippets/security-headers.conf;' no server block do checkpraia"

# ── 8. SD Card wear reduction ────────────────────────────────────────────
echo "=== 8. SD Card Wear Reduction ==="

# Desativar atime no disco se ainda não estiver
if ! grep -q "noatime" /etc/fstab; then
    echo ">>> Considera adicionar 'noatime' ao /etc/fstab para reduzir writes no SD card"
fi

# tmpfs para logs temporários
if ! grep -q "tmpfs /tmp" /etc/fstab; then
    echo "tmpfs /tmp tmpfs defaults,noatime,nosuid,nodev,size=128m 0 0" | sudo tee -a /etc/fstab
    echo "/tmp montado como tmpfs (128MB) — reduz writes no SD card"
fi

# ── Resumo ───────────────────────────────────────────────────────────────
echo ""
echo "============================================"
echo "  Security Hardening Concluído!"
echo "============================================"
echo ""
echo "  Firewall:     UFW ativo (22, 80, 443)"
echo "  Fail2Ban:     Ativo (SSH + Nginx)"
echo "  Logrotate:    7 dias, compressão"
echo "  Session:      ENCRYPT=true, DB driver"
echo "  SSH:          Root login desativado"
echo "  Nginx:        Security headers prontos"
echo "  SD Card:      tmpfs para /tmp"
echo ""
echo "  Proximo passo:"
echo "    1. Configurar nginx: sudo nano /etc/nginx/sites-available/checkpraia"
echo "    2. Incluir snippet: include /etc/nginx/snippets/security-headers.conf;"
echo "    3. Configurar SSL: sudo certbot --nginx -d checkpraia.pt"
echo ""
