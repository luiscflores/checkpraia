# CheckPraia - Developer Setup Guide

## Prerequisites

* **PHP 8.4** (required by composer.json)
* **Composer**
* **Node.js 22+** & **NPM**
* **SQLite** (included with PHP)
* **Git**

---

## 1. Local Setup

```bash
git clone <repo-url> checkpraia && cd checkpraia
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
```

This seeds 76 official Portuguese beaches and the default admin:
* **Email**: `admin@checkpraia.pt`
* **Password**: (set in `.env` → `ADMIN_PASSWORD`)

---

## 2. Development Server

```bash
composer dev
```

Runs concurrently: `php artisan serve`, `queue:listen`, `pail`, `vite`, `schedule:work`.

* **App**: [http://127.0.0.1:8000/pt](http://127.0.0.1:8000/pt)
* **Admin**: `/admin/dashboard`

---

## 3. Tests

```bash
./vendor/bin/phpunit
```

---

## 4. Production Deployment (Raspberry Pi 3)

The RPi3 is **internet-exposed** via port forwarding on the home router. DNS points `checkpraia.pt` to the public IP.

### First-time setup on the Pi

```bash
# SSH into the Pi
ssh pi@<RPi_IP>

# Clone and run setup
git clone --depth 1 https://github.com/luiscflores/checkpraia.git
cd checkpraia
bash setup-pi.sh
```

The setup script installs and configures:
- PHP 8.4 FPM + OPCache/JIT
- Nginx (SSL, gzip, rate limiting, security headers)
- Supervisor (queue worker)
- Cron (scheduler + auto-deploy every 5min)
- UFW firewall (22, 80, 443 only)
- Fail2Ban (SSH + Nginx brute-force + rate-limit)
- Certbot (auto-renewal)
- Kernel hardening (SYN flood, ICMP redirect)

### SSL certificate (required for production)

```bash
# After DNS propagates to the Pi's public IP:
sudo certbot --nginx -d checkpraia.pt

# Verify auto-renewal:
sudo certbot renew --dry-run
```

### Deploy via git push

```bash
# From your dev machine
git remote add pi ssh://pi@<RPi_PUBLIC_IP>/home/pi/checkpraia.git
git push pi main
```

The `post-receive` hook runs `scripts/deploy.sh` automatically.

### Deploy via cron (auto-pull from GitHub)

Every 5 minutes, cron runs `scripts/deploy.sh` which:
1. Fetches from GitHub and compares SHAs — **skips if no changes**
2. Installs composer dependencies (no-dev, optimized)
3. Builds frontend **only if** `resources/`, `package.json`, or `vite.config` changed
4. Runs migrations, caches config/routes/views/events
5. Reloads PHP-FPM and invalidates OPcache (USR2 signal)
6. Restarts queue worker via Supervisor

### Deploy features

| Feature | Detail |
|---------|--------|
| Lock | `flock` prevents concurrent deploys |
| Skip-if-nothing | Compares git SHAs before heavy ops |
| Rollback | Reverts to previous commit on failure |
| Frontend skip | Only rebuilds if frontend files changed |
| OPcache invalid | USR2 signal to PHP-FPM master PID |
| Zero-downtime | `reload` instead of `restart` on PHP-FPM |

### Monitoring

```bash
# Deploy logs
tail -f storage/logs/deploy.log

# Queue worker
sudo supervisorctl status

# Nginx errors
tail -f /var/log/nginx/error.log

# Fail2Ban status
sudo fail2ban-client status
sudo fail2ban-client status nginx-limit-req

# OPcache status
php -r "echo json_encode(opcache_get_status(), JSON_PRETTY_PRINT);"

# SSL certificate expiry
sudo certbot certificates
```

### Useful commands on the Pi

```bash
# Manual deploy
cd /home/pi/checkpraia && bash scripts/deploy.sh

# Reload nginx after config change
sudo nginx -t && sudo systemctl reload nginx

# Clear all caches
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Check OPcache memory
php -r "var_dump(opcache_get_status()['memory_usage']);"
```

---

## 5. Environment Variables

Key variables for production (`.env`):

| Variable | Production Value | Notes |
|----------|-----------------|-------|
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | Never expose stack traces |
| `APP_URL` | `https://checkpraia.pt` | Must be HTTPS |
| `DB_CONNECTION` | `sqlite` | No server needed |
| `SESSION_DRIVER` | `database` | Encrypted sessions |
| `SESSION_ENCRYPT` | `true` | Required for security |
| `SESSION_SECURE_COOKIE` | `true` | HTTPS only |
| `QUEUE_CONNECTION` | `database` | Supervisor processes jobs |
| `CACHE_STORE` | `file` | Lightweight for RPi3 |
