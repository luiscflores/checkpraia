# CheckPraia

A platform for monitoring beach conditions along the Portuguese coast, combining official forecasts, community reports, and real-time data.

## Features

- **Dynamic Flag Attribution** — Official alerts > Community consensus > Automated forecast (IPMA/Open-Meteo)
- **76 Official Portuguese Beaches** — GPS coordinates, water quality classifications, live weather & swell data
- **Community Reports** — GPS-validated flag reports with consensus voting and anti-spam scoring
- **Gamification** — Points, trust levels, referral milestones
- **PWA Mobile Interface** — Glassmorphism UI built with Livewire 4 + Tailwind 4
- **Admin Dashboard** — Filament 3 backoffice panel
- **Push Notifications** — Web Push (VAPID) for nearby beach alerts

## Stack

| Layer | Tech |
|-------|------|
| Backend | Laravel 13 / PHP 8.4 |
| Frontend | Livewire 4 / Volt / Tailwind 4 / Vite 8 |
| Database | SQLite (WAL mode) |
| Queue | Laravel Queue (database driver, Supervisor) |
| Server | Nginx + PHP-FPM on Raspberry Pi 3 (1GB RAM) |
| Deploy | Git bare repo + post-receive hook |

## Deployment (Raspberry Pi 3)

### First-time setup

```bash
# 1. Clone on the Pi
git clone https://github.com/luiscflores/checkpraia.git
cd checkpraia

# 2. Run the setup script (installs PHP, Nginx, Supervisor, etc.)
bash setup-pi.sh
```

### Deploy via git push

```bash
# From your development machine
git remote add pi ssh://pi@192.168.1.212/home/pi/checkpraia.git
git push pi main
```

### Deploy via cron (auto-pull)

The setup script configures a cron job that runs `scripts/deploy.sh` every 5 minutes, pulling from GitHub and rebuilding automatically.

### Deploy features

- **Lock** — prevents concurrent deploys
- **Skip if no changes** — compares git SHAs before running heavy operations
- **Rollback** — reverts to previous commit on failure
- **Frontend skip** — only runs `npm build` if `resources/`, `package.json`, or `vite.config` changed
- **OPcache invalidation** — triggers `USR2` signal to PHP-FPM after deploy

## Development

```bash
# Install dependencies
composer install
npm install

# Setup
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed

# Run dev server
composer dev
```

## Architecture

See [ARCHITECTURE.md](ARCHITECTURE.md) for the full system design, data sources, and flag resolution logic.

## Security

See [SECURITY_AUDIT.md](SECURITY_AUDIT.md) for the infrastructure audit and hardening checklist.

## License

MIT
