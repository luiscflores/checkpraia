#!/bin/bash
# ── CheckPraia Deploy - Optimized for Raspberry Pi 3 ───────────────────────
# Lock + rollback + skip-if-nothing-changed + OPcache tuning
set -euo pipefail

APP_DIR="${1:-/var/www/checkpraia}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.4-fpm}"
SUPERVISOR_PROGRAM="${SUPERVISOR_PROGRAM:-checkpraia-worker}"
DEPLOY_LOCK="/tmp/checkpraia-deploy.lock"
DEPLOY_LOG="$APP_DIR/storage/logs/deploy.log"
ROLLBACK_REF=""

# ── Lock: prevent concurrent deploys ──────────────────────────────────────
exec 200>"$DEPLOY_LOCK"
flock -n 200 || { echo ">>> Deploy already running. Skipping."; exit 0; }
trap 'rm -f "$DEPLOY_LOCK"' EXIT

# ── Helpers ────────────────────────────────────────────────────────────────
log()  { echo ">>> $(date '+%H:%M:%S') $*" | tee -a "$DEPLOY_LOG"; }
fail() { log "FAILED: $*"; rollback; exit 1; }

rollback() {
    if [ -n "$ROLLBACK_REF" ]; then
        log "Rolling back to $ROLLBACK_REF"
        cd "$APP_DIR" && git checkout "$ROLLBACK_REF" -- . 2>/dev/null || true
    fi
}

# ── 1. Pull changes ───────────────────────────────────────────────────────
cd "$APP_DIR"

SKIP_GIT=0
if [ "${2:-}" = "--no-git" ] || [ ! -d .git ]; then
    SKIP_GIT=1
fi

if [ "$SKIP_GIT" -eq 0 ]; then
    PREV_SHA=$(git rev-parse HEAD 2>/dev/null || echo "none")
    ROLLBACK_REF="$PREV_SHA"

    git fetch origin main --quiet 2>/dev/null
    NEW_SHA=$(git rev-parse origin/main)

    if [ "$PREV_SHA" = "$NEW_SHA" ]; then
        log "No changes ($PREV_SHA). Skipping deploy."
        exit 0
    fi

    log "Deploy: $PREV_SHA -> $NEW_SHA"
    git reset --hard origin/main --quiet
else
    log "Skipping Git operations (--no-git flag or no .git directory)"
fi

# ── 2. Ensure directories + SQLite (MUST be before composer install) ─────────
# composer's post-autoload-dump triggers `php artisan package:discover`
# which tries to connect to SQLite — the file must exist before that.
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views
mkdir -p bootstrap/cache database storage/logs

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        php artisan key:generate --no-interaction
        log ".env created from .env.example — edit it before continuing."
        # Don't exit — continue so the rest of the deploy can finish
    fi
fi

# Create SQLite file BEFORE composer install
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    log "SQLite database file created."
fi

# Ensure www-data can read/write the database
chmod 664 database/database.sqlite 2>/dev/null || true
sudo chown www-data:www-data database/database.sqlite 2>/dev/null || true
chmod 2775 database 2>/dev/null || true

# ── 3. Composer (fast flags for Pi) ───────────────────────────────────────
# IMPORTANT: --no-scripts prevents composer from running post-autoload-dump
# hooks (package:discover) which crash if database/database.sqlite is missing.
# We run package:discover manually below after SQLite is guaranteed to exist.
export COMPOSER_ALLOW_SUPERUSER=1
composer install \
    --no-dev \
    --optimize-autoloader \
    --classmap-authoritative \
    --no-interaction \
    --no-progress \
    --no-suggest \
    --prefer-dist \
    --no-scripts \
    --quiet

# Now that composer is done and SQLite exists, run artisan bootstrap safely
php artisan package:discover --ansi 2>/dev/null || true

# ── 4. Frontend build (cached via content hashing) ────────────────────────────
if [ -d resources ]; then
    CURRENT_HASH=$(find resources package-lock.json vite.config.js -type f 2>/dev/null | sort | xargs md5sum 2>/dev/null | md5sum | awk '{print $1}')
else
    CURRENT_HASH="force"
fi
PREV_HASH=""
if [ -f storage/framework/frontend-build-hash ]; then
    PREV_HASH=$(cat storage/framework/frontend-build-hash)
fi

if [ "$CURRENT_HASH" != "$PREV_HASH" ] || [ ! -f "public/build/manifest.json" ]; then
    log "Frontend changed or manifest missing — building assets..."
    npm ci --ignore-scripts --no-audit --no-fund --prefer-offline --quiet 2>/dev/null || npm install --no-audit --no-fund --quiet 2>/dev/null || true
    npm run build --quiet 2>/dev/null
    rm -rf node_modules
    mkdir -p storage/framework
    echo "$CURRENT_HASH" > storage/framework/frontend-build-hash
else
    log "Frontend unchanged — skipping npm build."
fi

# ── 5. Migrations ────────────────────────────────────────────────────────
php artisan migrate --force --quiet 2>/dev/null || true

# ── 6. Clear old cache, then rebuild ────────────────────────────────────
php artisan optimize:clear --quiet 2>/dev/null || true
php artisan config:cache --quiet 2>/dev/null || true
php artisan route:cache --quiet 2>/dev/null || true
php artisan view:cache --quiet 2>/dev/null || true
php artisan event:cache --quiet 2>/dev/null || true
php artisan storage:link --no-interaction 2>/dev/null || true

# ── 7. Permissions (fast, targeted) ──────────────────────────────────────
chmod -R g+rwX storage bootstrap/cache database 2>/dev/null || true
# PHP-FPM runs as www-data — it must OWN cache dirs to avoid touch(): utime failed
sudo chown -R www-data:www-data storage/framework 2>/dev/null || true

# ── 8. Permissões finais (garantir após deploy) ───────────────────────────
sudo chown -R www-data:www-data \
    storage/framework \
    storage/logs \
    bootstrap/cache \
    database 2>/dev/null || true

# ── 9. Reload services (avoid full restart for zero-downtime) ────────────
_PHP_FPM=$(systemctl list-units --type=service --state=active 2>/dev/null | grep 'php.*fpm' | awk '{print $1}' | head -1)
_PHP_FPM="${_PHP_FPM:-$PHP_FPM_SERVICE}"
sudo systemctl reload "$_PHP_FPM" 2>/dev/null \
    || sudo systemctl restart "$_PHP_FPM" 2>/dev/null \
    || log "Warning: PHP-FPM reload failed"

# Invalidate OPcache: send USR2 to all PHP-FPM workers via PID file
_PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "8.4")
FPM_PID="/var/run/php/php${_PHP_VERSION}-fpm.pid"
if [ -f "$FPM_PID" ]; then
    sudo kill -USR2 "$(cat "$FPM_PID")" 2>/dev/null || true
else
    # Fallback: find by process name
    sudo pkill -USR2 -f "php-fpm: master" 2>/dev/null || true
fi

sudo supervisorctl restart "$SUPERVISOR_PROGRAM:*" 2>/dev/null \
    || log "Warning: supervisorctl restart failed"

# ── 9. Done ──────────────────────────────────────────────────────────────
if [ "$SKIP_GIT" -eq 0 ]; then
    log "Deploy complete: $NEW_SHA"
else
    log "Deploy complete: Git operations skipped (--no-git)"
fi
