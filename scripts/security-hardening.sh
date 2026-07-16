#!/bin/bash
# ── Security Hardening - CheckPraia RPi3 (Internet-Exposed) ────────────────
# Firewall + Fail2Ban + SSL + Logrotate + SSH + Rate Limiting
set -euo pipefail

WORK_TREE="${WORK_TREE:-/var/www/checkpraia}"
APP_ENV="$WORK_TREE/.env"

echo ""
echo "============================================"
echo "  Security Hardening - CheckPraia RPi3"
echo "  Production: Internet-Exposed"
echo "============================================"
echo ""

# ── 1. Firewall (UFW) ────────────────────────────────────────────────────
echo "=== 1. Configurar Firewall (UFW) ==="

sudo apt-get install -y -qq ufw 2>/dev/null || true

sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow 22/tcp comment 'SSH'
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'

sudo ufw --force enable
sudo ufw status verbose

# ── 2. Fail2Ban (SSH + Nginx brute-force + rate-limit) ──────────────────
echo "=== 2. Instalar Fail2Ban ==="

sudo apt-get install -y -qq fail2ban 2>/dev/null || true

sudo tee /etc/fail2ban/jail.local > /dev/null << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5
banaction = ufw

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 7200

[nginx-http-auth]
enabled = true
port = http,https
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3

[nginx-limit-req]
enabled = true
port = http,https
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 5
findtime = 120
bantime = 600

[nginx-botsearch]
enabled = true
port = http,https
filter = nginx-botsearch
logpath = /var/log/nginx/access.log
maxretry = 2
bantime = 86400
EOF

sudo systemctl enable fail2ban
sudo systemctl restart fail2ban

# ── 3. Certbot (SSL Let's Encrypt) ───────────────────────────────────────
echo "=== 3. Configurar SSL (Certbot) ==="

if ! command -v certbot &>/dev/null; then
    sudo apt-get install -y -qq certbot python3-certbot-nginx 2>/dev/null || true
fi

# Auto-renewal timer
if ! systemctl is-active --quiet certbot.timer 2>/dev/null; then
    sudo systemctl enable certbot.timer 2>/dev/null || true
    sudo systemctl start certbot.timer 2>/dev/null || true
fi

# Crontab fallback for cert renewal (belt and suspenders)
CRON_CERT="0 3 * * * certbot renew --quiet --post-hook 'systemctl reload nginx'"
(crontab -l 2>/dev/null | grep -v "certbot renew"; echo "$CRON_CERT") | crontab - 2>/dev/null || true

if [ ! -f /etc/letsencrypt/live/checkpraia.pt/fullchain.pem ]; then
    echo ">>> SSL certificate not found."
    echo ">>> Run: sudo certbot --nginx -d checkpraia.pt"
    echo ">>> Or if DNS not ready: sudo certbot certonly --standalone -d checkpraia.pt"
else
    echo "SSL certificate found."
    # Verify renewal works
    sudo certbot renew --dry-run 2>/dev/null && echo "Cert renewal OK." || echo ">>> Warning: cert renewal test failed."
fi

# ── 4. Logrotate ─────────────────────────────────────────────────────────
echo "=== 4. Configurar Logrotate ==="

sudo tee /etc/logrotate.d/checkpraia > /dev/null << 'EOF'
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

/var/log/nginx/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data adm
    sharedscripts
    postrotate
        [ -f /var/run/nginx.pid ] && sudo kill -USR1 $(cat /var/run/nginx.pid) 2>/dev/null || true
    endscript
}
EOF

echo "Logrotate: app 7 dias + nginx 14 dias."

# ── 5. Session hardening ─────────────────────────────────────────────────
echo "=== 5. Session Hardening ==="

if [ -f "$APP_ENV" ]; then
    if grep -q "SESSION_ENCRYPT=false" "$APP_ENV"; then
        sed -i 's/SESSION_ENCRYPT=false/SESSION_ENCRYPT=true/' "$APP_ENV"
        echo "SESSION_ENCRYPT=true"
    fi
    if grep -q "SESSION_DRIVER=file" "$APP_ENV"; then
        sed -i 's/SESSION_DRIVER=file/SESSION_DRIVER=database/' "$APP_ENV"
        echo "SESSION_DRIVER=database"
    fi
    # Ensure secure cookie in production
    if grep -q "SESSION_SECURE_COOKIE=false" "$APP_ENV"; then
        sed -i 's/SESSION_SECURE_COOKIE=false/SESSION_SECURE_COOKIE=true/' "$APP_ENV"
        echo "SESSION_SECURE_COOKIE=true"
    fi
fi

# ── 6. SSH Hardening ─────────────────────────────────────────────────────
echo "=== 6. SSH Hardening ==="

sudo sed -i 's/#PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config 2>/dev/null || true
sudo sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config 2>/dev/null || true

# Keep password auth ENABLED so any device can SSH in
sudo mkdir -p /etc/ssh/sshd_config.d
echo "PasswordAuthentication yes" | sudo tee /etc/ssh/sshd_config.d/checkpraia.conf > /dev/null
echo "KbdInteractiveAuthentication yes" | sudo tee -a /etc/ssh/sshd_config.d/checkpraia.conf

sudo systemctl restart sshd 2>/dev/null || true
echo "SSH: root login disabled, password auth enabled."

# ── 7. Auto-security-updates ─────────────────────────────────────────────
echo "=== 7. Atualizacoes automaticas de seguranca ==="

sudo apt-get install -y -qq unattended-upgrades 2>/dev/null || true
sudo dpkg-reconfigure -plow unattended-upgrades 2>/dev/null || true

# ── 8. Nginx rate limit snippet ──────────────────────────────────────────
echo "=== 8. Nginx Rate Limiting ==="

sudo tee /etc/nginx/snippets/rate-limit.conf > /dev/null << 'EOF'
# Rate limiting zones (must be in http context)
# 10 req/sec per IP for general, 2 req/sec for auth
limit_req_zone $binary_remote_addr zone=general:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=auth:10m rate=2r/s;
limit_req_status 429;
EOF

# Include rate-limit.conf in nginx.conf if not already included
if ! grep -q "rate-limit.conf" /etc/nginx/nginx.conf 2>/dev/null; then
    sudo sed -i '/http {/a \    include /etc/nginx/snippets/rate-limit.conf;' /etc/nginx/nginx.conf 2>/dev/null || true
    echo "Rate limit snippet included in nginx.conf."
fi

# ── 9. SD Card wear reduction ────────────────────────────────────────────
echo "=== 9. SD Card Wear Reduction ==="

if ! grep -q "noatime" /etc/fstab; then
    sudo sed -i 's/errors=remount-ro/errors=remount-ro,noatime/' /etc/fstab 2>/dev/null || true
    echo "noatime adicionado."
fi

if ! grep -q "tmpfs /tmp" /etc/fstab; then
    echo "tmpfs /tmp tmpfs defaults,noatime,nosuid,nodev,size=128m 0 0" | sudo tee -a /etc/fstab
    echo "/tmp como tmpfs."
fi

# ── 10. Kernel hardening (network) ───────────────────────────────────────
echo "=== 10. Kernel Network Hardening ==="

sudo tee /etc/sysctl.d/99-checkpraia.conf > /dev/null << 'EOF'
# Disable IP forwarding (not a router)
net.ipv4.ip_forward=0

# Ignore ICMP redirects
net.ipv4.conf.all.accept_redirects=0
net.ipv4.conf.default.accept_redirects=0
net.ipv6.conf.all.accept_redirects=0

# Don't send ICMP redirects
net.ipv4.conf.all.send_redirects=0
net.ipv4.conf.default.send_redirects=0

# SYN flood protection
net.ipv4.tcp_syncookies=1
net.ipv4.tcp_max_syn_backlog=2048
net.ipv4.tcp_synack_retries=2

# Log suspicious packets
net.ipv4.conf.all.log_martians=1
EOF

sudo sysctl -p /etc/sysctl.d/99-checkpraia.conf 2>/dev/null || true

# ── Resumo ───────────────────────────────────────────────────────────────
echo ""
echo "============================================"
echo "  Security Hardening Concluido!"
echo "============================================"
echo ""
echo "  Firewall:     UFW ativo (22, 80, 443)"
echo "  Fail2Ban:     SSH + Nginx auth + rate-limit"
echo "  SSL:          Certbot + auto-renew"
echo "  Logrotate:    App 7d + Nginx 14d"
echo "  Session:      ENCRYPT=true, SECURE_COOKIE=true"
echo "  SSH:          Root desativado, password ativo"
echo "  Rate Limit:   10r/s geral, 2r/s auth"
echo "  Kernel:       SYN flood + ICMP redirect protecao"
echo "  SD Card:      tmpfs /tmp + noatime"
echo ""
echo "  PROXIMO PASSO (obrigatorio para internet):"
echo "    sudo certbot --nginx -d checkpraia.pt"
echo ""
