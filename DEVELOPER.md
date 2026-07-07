# CheckPraia - Developer Setup Guide

This guide helps you configure and run the CheckPraia platform (Laravel 13 + Livewire 4 + SQLite) on your local computer.

## Prerequisites
Before starting, ensure you have the following installed on your machine:
* **PHP 8.2 or 8.3+** (or PHP 8.5)
* **Composer** (PHP dependency manager)
* **Node.js 18+** & **NPM**
* **SQLite** (included with PHP, no separate server needed)
* **Git**

---

## 1. Local Repository Setup

Clone the project and enter the directory:
```bash
git clone <repository-url> checkpraia
cd checkpraia
```

Install the PHP composer dependencies:
```bash
composer install
```

Install the Node/frontend dependencies:
```bash
npm install
```

---

## 2. Environment Configuration

Copy the example environment file:
```bash
cp .env.example .env
```

Open `.env` and check the database settings. By default, CheckPraia uses SQLite:
```env
DB_CONNECTION=sqlite
```

Generate the Laravel application key:
```bash
php artisan key:generate
```

---

## 3. Database Initialization

CheckPraia uses **SQLite** by default — no database server required:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

This registers the **76 official coastal beaches of Portugal** and the default administrator user:
* **Email**: `luis@checkpraia.pt`
* **Password**: `password`

---

## 4. Live API Sync & Caching

To retrieve live weather and wave forecasts from **Open-Meteo**, classifications from **dados.gov.pt**, and local dining information from **OpenStreetMap**:
```bash
# 1. Update forecasts and calculate flags:
php artisan tinker --execute="App\Jobs\FetchIpmaForecasts::dispatchSync(); App\Jobs\FetchInfoAguaData::dispatchSync();"

# 2. Update local TripAdvisor and TheFork restaurants:
php artisan tinker --execute="foreach (\App\Models\Beach::all() as \$b) { (new \App\Services\Tripadvisor\TripadvisorClient())->getNearby(\$b->latitude, \$b->longitude); }"
```

To run this automatically in production, configure the Laravel scheduler inside your crontab:
```cron
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Running the Application locally

Start the PHP Artisan development server:
```bash
php artisan serve --port=8000
```

In a separate terminal, compile and serve the Vite/Tailwind 4 CSS assets:
```bash
npm run dev
```

Open your browser and navigate to:
* **Application**: [http://127.0.0.1:8000/pt](http://127.0.0.1:8000/pt)
* **Backoffice Dashboard**: Log in with credentials and access `/admin/dashboard`

---

## 6. Execution of Automated Tests

CheckPraia includes a comprehensive feature test suite covering GPS validation, referrals, and consensus overrides. Run the tests using PHPUnit:
```bash
./vendor/bin/phpunit
```
