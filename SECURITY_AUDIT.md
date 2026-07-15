# CheckPraia — Security & Infrastructure Audit

**Date:** 2025-07-15  
**Target:** Raspberry Pi 3 (1GB RAM), internet-exposed via router  
**Stack:** Laravel 13.8 / Livewire 3 / Volt / Filament 3 / Tailwind 4 / SQLite / PHP 8.4-FPM / Nginx

---

## 1 — CRITICAL: Secrets in `.env`

| Secret | Location | Risk |
|--------|----------|------|
| `APP_KEY` | `.env:3` | Session encryption key. If leaked, attacker can forge sessions |
| `ADMIN_PASSWORD` | `.env:14` | Hardcoded admin password `CheckPraia2026!` |
| `VAPID_PRIVATE_KEY` | `.env:67` | Web-push signing key. Leaked = attacker sends push as you |
| `GOOGLE_CLIENT_SECRET` | `.env:74` | OAuth secret. Leaked = attacker hijacks Google accounts |

**Git status:**
- `.env` — **NOT tracked** ✓ (empty from `git log`)
- `.env.example` — NOT tracked ✓
- `.env.render` — **IS tracked** in git history (3 commits: `0a6a068`, `fef6871`, `a32f977`). The current working copy has **empty values** so no real leak, but it reveals the expected variable names and structure to anyone reading the repo.

**Action items:**
1. Rotate `APP_KEY` immediately — `php artisan key:generate`
2. Change the admin password to something not in version control
3. Regenerate VAPID keys (`web-push:vapid` or manually)
4. Rotate `GOOGLE_CLIENT_SECRET` in Google Cloud Console
5. Add `.env.render` to `.gitignore` and `git rm --cached .env.render`
6. **Never commit `.env` to any branch**

---

## 2 — CRITICAL: `SESSION_ENCRYPT=false`

**File:** `.env:29`

Laravel sessions contain serialized data. With encryption off, session files on disk are readable by anyone with filesystem access. On a Raspberry Pi shared with other services or if the disk is compromised, the attacker gets user session tokens in plaintext.

**Fix:** Set `SESSION_ENCRYPT=true` in `.env`

---

## 3 — HIGH: No HTTPS / No TLS

**File:** `scripts/checkpraia-nginx.conf`

The nginx config serves on port 80 only. No SSL/TLS certificate is configured.

**Consequences:**
- All traffic (including login, session cookies, admin panel) transmitted in plaintext
- Session cookies can be intercepted (session hijacking)
- `SESSION_SECURE_COOKIE` (set in `.env.render` but not `.env`) won't work without TLS — the cookie simply won't be sent
- Google OAuth redirect URI is currently `http://localhost:8000` — won't work in production

**Fix:**
1. Install Certbot: `sudo apt install certbot python3-certbot-nginx`
2. Get certificate: `sudo certbot --nginx -d checkpraia.pt`
3. Update `APP_URL` and `GOOGLE_REDIRECT_URI` in `.env` to `https://checkpraia.pt`
4. Set `SESSION_SECURE_COOKIE=true` in `.env`

---

## 4 — HIGH: No Firewall / No SSH Hardening

**File:** `setup-pi.sh`

The setup script installs nginx and PHP-FPM but does **nothing** for network security:

| Missing | Risk |
|---------|------|
| `ufw` / iptables | All ports open to internet |
| fail2ban | SSH brute-force attacks |
| SSH key-only auth | Password SSH = brute-force target |
| Port restriction | Services (Redis, DB) could be reachable |
| `unattended-upgrades` | Unpatched OS = easy exploit |

**Fix:** Add to `setup-pi.sh`:
```bash
sudo apt install -y ufw fail2ban unattended-upgrades
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw --force enable
sudo systemctl enable fail2ban
sudo dpkg-reconfigure -plow unattended-upgrades
```

---

## 5 — HIGH: No Rate Limiting on Favorite Toggle

**File:** `routes/web.php:105`, `app/Http/Controllers/FavoriteController.php`

```php
Route::post('/favorites/toggle', [FavoriteController::class, 'toggle'])
    ->name('favorites.toggle');
```

No `throttle` middleware. An unauthenticated or authenticated user can spam this endpoint:
- Floods the database with favorite records
- Could be used for denial-of-service
- `FavoriteController::toggle()` has **no validation** that `beach_id` exists before creating the favorite

**Fix:** Add `->middleware('throttle:30,1')` and validate `beach_id` exists in the controller.

---

## 6 — HIGH: User Model Mass-Assignable `is_admin` and `is_suspended`

**File:** `app/Models/User.php`

The `User` model uses the `Fillable` attribute (Laravel 11+) which marks `is_admin` and `is_suspended` as mass-assignable. Combined with Filament's admin dashboard that allows score adjustments and suspensions, this creates a risk:

- If any code path does `User::update($request->all())` or similar, an attacker can escalate to admin
- The Filament `Dashboard.php` component does admin actions — these need careful audit

**Fix:** Move `is_admin` and `is_suspended` to `protected $guarded = []` pattern, or ensure they're never in mass-assignment contexts.

---

## 7 — MEDIUM: No Log Rotation

**File:** `setup-pi.sh`, `storage/logs/`

No `logrotate` configuration exists. On a Raspberry Pi with a small SD card:
- Laravel logs grow unbounded
- Queue worker logs (managed by supervisor) are configured with `maxbytes=10MB` but no `numfiles` rotation
- OPcache logs, PHP-FPM logs, nginx logs — none rotated

**SD cards have limited write cycles. Filling the disk = app crash.**

**Fix:** Add logrotate config:
```bash
cat > /etc/logrotate.d/checkpraia << 'EOF'
/home/luisflores/LAB/checkpraia/storage/logs/*.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    copytruncate
}
EOF
```

---

## 8 — MEDIUM: OPcache JIT Buffer Too Large for RPi3

**File:** `scripts/php-opcache-jit.ini`

```ini
opcache.jit_buffer_size=128M
opcache.memory_consumption=256M
opcache.max_accelerated_files=10000
```

On a Raspberry Pi 3 with **1GB total RAM**:
- OPcache alone: 256MB
- JIT buffer: 128MB
- PHP-FPM workers (×2): ~80MB each
- Kernel + OS: ~150MB
- **Total: ~674MB** — leaving only ~300MB for file cache and buffers

This will cause heavy swapping, which kills SD card lifespan and tanks performance.

**Fix:** Reduce to:
```ini
opcache.jit_buffer_size=32M
opcache.memory_consumption=64M
opcache.max_accelerated_files=4000
opcache.interned_strings_buffer=8
```

---

## 9 — MEDIUM: No CSRF Protection on `/filament` Routes (Check)

**File:** `bootstrap/app.php`

Laravel 11 includes `VerifyCsrfToken` by default in the web middleware group. Filament's routes are registered via its service provider and included in the web middleware group. **CSRF is protected** ✓ — but worth verifying Filament's own admin auth doesn't bypass it.

---

## 10 — MEDIUM: Locale Switcher — Open Redirect

**File:** `routes/web.php:33-83`

The locale switcher reads the `Referer` header and redirects back. The `parse_url` + path manipulation is relatively safe (no external URLs), but the `$referrer` is used directly in `redirect()`. While the code only modifies internal paths, there's no explicit check that the referer is same-origin.

**Risk:** Low — the code filters for internal paths, but a crafted referer with special characters could potentially cause header injection.

**Fix:** Validate the referer is a local path before using it.

---

## 11 — MEDIUM: Filament Installed but Not Routed

**Finding:** `filament/filament` is in `composer.json` dependencies, but:
- No `Filament\PanelProvider` found in `app/Providers/`
- No `/admin` or `/filament` routes in `routes/web.php`
- No Filament panel configuration file

**Status:** Filament is a dead dependency consuming memory and increasing attack surface. If not used, remove it:
```bash
composer remove filament/filament
```

If it IS used somewhere I can't find, it needs auth middleware (Filament handles this by default, but verify the panel is properly locked down).

---

## 12 — LOW: Queue Worker Single Process

**File:** `checkpraia-worker.conf`

```ini
numprocs=1
```

With only 1 worker, if a job fails or hangs (e.g., IPMA API timeout), the queue backs up. On RPi3 this is acceptable to conserve memory, but ensure jobs have proper timeouts:

```php
// Already in most jobs via ShouldQueue
public int $timeout = 60;
public int $tries = 3;
```

---

## 13 — LOW: `APP_DEBUG=false` ✓

**File:** `.env:5`

Already set correctly. Stack traces won't leak to users. ✓

---

## 14 — LOW: No Content Security Policy (CSP)

**File:** `scripts/checkpraia-nginx.conf`

The nginx config has some security headers but **no CSP**:

```
add_header X-Frame-Options "SAMEORIGIN";
add_header X-Content-Type-Options "nosniff";
add_header Referrer-Policy "strict-origin-when-cross-origin";
add_header X-XSS-Protection "1; mode=block";
```

Missing: `Content-Security-Policy`, `Strict-Transport-Security` (HSTS), `Permissions-Policy`.

**Fix:** Add after getting HTTPS:
```nginx
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self' https://checkpraia.pt;" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header Permissions-Policy "camera=(), microphone=(), geolocation=(self)" always;
```

---

## 15 — LOW: Sitemap Query

**File:** `routes/web.php:129`

```php
$beaches = Beach::select('slug', 'updated_at')->get();
```

Not a vulnerability, but loads all beaches into memory on every request. On RPi3 with limited RAM, use cursor or chunking:
```php
Beaches::select('slug', 'updated_at')->orderBy('slug')->cursor();
```

---

## Deployment Checklist

```
[  ] 1.  Rotate APP_KEY, admin password, VAPID keys, Google secret
[  ] 2.  Set SESSION_ENCRYPT=true
[  ] 3.  Install nginx SSL (certbot)
[  ] 4.  Update APP_URL and GOOGLE_REDIRECT_URI to https://checkpraia.pt
[  ] 5.  Set SESSION_SECURE_COOKIE=true
[  ] 6.  Add firewall (ufw), fail2ban, SSH hardening to setup-pi.sh
[  ] 7.  Add throttle to /favorites/toggle
[  ] 8.  Validate beach_id in FavoriteController::toggle()
[  ] 9.  Add logrotate config
[  ] 10. Reduce OPcache/JIT to 32MB/64MB
[  ] 11. Add CSP and HSTS headers to nginx
[  ] 12. Remove or configure Filament properly
[  ] 13. git rm --cached .env.render && add to .gitignore
[  ] 14. Set APP_ENV=production (already set ✓)
[  ] 15. Verify SQLite database permissions and WAL mode
[  ] 16. Test all scheduled jobs in production context
```

---

*Generated by opencode audit session*
