# CheckPraia - Developer Setup Guide

This guide helps you configure and run the CheckPraia platform (Laravel 13 + Livewire 4 + PostgreSQL) on your local computer.

## Prerequisites
Before starting, ensure you have the following installed on your machine:
* **PHP 8.2 or 8.3+** (or PHP 8.5)
* **Composer** (PHP dependency manager)
* **Node.js 18+** & **NPM**
* **Docker** & **Docker Compose** (for the PostgreSQL database)
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

Open `.env` and adjust the PostgreSQL database credentials. By default, CheckPraia runs on a local Docker PostgreSQL container:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mls
DB_USERNAME=postgres
DB_PASSWORD=secret
```

Generate the Laravel application key:
```bash
php artisan key:generate
```

---

## 3. Database Initialization (Docker Compose)

A standard local PostgreSQL instance can be launched via Docker. If you have Docker Compose set up, ensure you have a container running.
Otherwise, boot a PostgreSQL instance directly:
```bash
docker run --name mls_postgres_local -e POSTGRES_USER=postgres -e POSTGRES_PASSWORD=secret -e POSTGRES_DB=mls -p 5432:5432 -d postgres:16
```

Once the database container is online, run the fresh migrations and seed the **76 official coastal beaches of Portugal**:
```bash
php artisan migrate:fresh --seed
```

This registers the default administrator user:
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
