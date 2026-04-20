# Deploy Laravel + Inertia App on Render with Supabase

This guide explains how to deploy `SSS Daily Transaction Logs` to Render using Supabase Postgres.

It covers:
- Supabase database setup
- Render web service setup
- Required environment variables
- Build/start commands
- Migrations and production checks
- Troubleshooting common failures

---

## 1) Prerequisites

Before deploying, make sure you have:
- A GitHub repository with this project pushed
- A Render account
- A Supabase account and project
- Local project runs successfully (`php artisan serve` + `npm run dev`)

Recommended local version targets:
- PHP 8.2+
- Node 20+
- Composer 2+

---

## 2) Prepare the app for production

From project root:

```bash
composer install
npm install
npm run build
php artisan key:generate
```

If this is a fresh setup, ensure migration files for database-backed session/cache/queue tables exist:

```bash
php artisan session:table
php artisan cache:table
php artisan queue:table
```

Then migrate locally once:

```bash
php artisan migrate
php artisan db:seed --class=RoleSeeder
```

> If migration files already exist, Laravel will tell you and skip duplicates.

---

## 3) Supabase: get database connection details

In Supabase project dashboard:

1. Open **Connect** (or the database connection panel in settings).
2. Copy:
   - Host (example: `db.<project-ref>.supabase.co`)
   - Port (`5432`)
   - Database (`postgres`)
   - User (`postgres`)
3. If you do not know the password, **reset DB password** in Supabase and use the new one.

Use SSL:
- `DB_SSLMODE=require`

---

## 4) Render: create a Web Service

1. In Render, click **New +** -> **Web Service**.
2. Connect your GitHub repo.
3. Set service values:

- **Runtime**: `PHP`
- **Build Command**:

```bash
composer install --no-dev --optimize-autoloader && npm install && npm run build
```

- **Start Command**:

```bash
php artisan migrate --force && php artisan optimize && php -S 0.0.0.0:$PORT -t public
```

> This command is simple and works on Render for small-medium apps.  
> For more advanced production traffic, move to nginx + php-fpm architecture.

---

## 5) Render environment variables

Add these in Render -> Service -> **Environment**.

```env
APP_NAME="SSS Daily Transaction Logs"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-render-service>.onrender.com
APP_KEY=base64:YOUR_APP_KEY
APP_TIMEZONE=Asia/Manila

APP_LOCALE=en
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=db.<your-project-ref>.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=YOUR_SUPABASE_DB_PASSWORD
DB_SSLMODE=require

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

Notes:
- `APP_KEY` must be set. Generate with `php artisan key:generate --show`.
- Set `APP_DEBUG=false` in production.
- `APP_URL` must match your Render URL (or custom domain).

---

## 6) Deploy flow (first deployment)

1. Push all code to GitHub.
2. Trigger Render deploy.
3. Watch deploy logs for:
   - Composer install success
   - Node install/build success
   - `php artisan migrate --force` success
4. Open Render URL and verify pages load.

---

## 7) Post-deploy checks

After first successful deploy:

1. Login as admin/intern.
2. Create a transaction from intern dashboard.
3. Verify admin dashboard "today" values update correctly.
4. Open transaction history and verify date grouping/time format.
5. Test profile update save buttons.
6. Export report CSV.

If any DB errors appear, re-check DB env vars and SSL mode.

---

## 8) Queue worker (optional but recommended)

If you process jobs asynchronously, create a second Render service as **Background Worker**:

- **Build Command**:

```bash
composer install --no-dev --optimize-autoloader
```

- **Start Command**:

```bash
php artisan queue:work --tries=3 --timeout=90
```

Use the same environment variables as the web service.

---

## 9) File storage note

Render filesystem is ephemeral unless persistent disk is configured.

If your app stores generated files/uploads long-term, prefer object storage (S3-compatible).  
For now, if signatures are stored in the database (base64), this is not affected by ephemeral disk.

---

## 10) Common issues and fixes

### A) "SQLSTATE connection refused" or timeout
- Check `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, `DB_SSLMODE=require`.
- Ensure Supabase project is active and reachable.

### B) "No application encryption key has been specified"
- Set `APP_KEY` in Render env.

### C) Changes not reflected after deploy
- Confirm deployment used latest commit.
- Restart service from Render dashboard.

### D) Migration/table errors for sessions/cache/jobs
- Ensure required migration files exist and were applied:
  - `sessions`
  - `cache`
  - `jobs`

### E) 500 error with no useful message
- Temporarily set `LOG_LEVEL=debug` and check Render logs.
- Keep `APP_DEBUG=false`.

---

## 11) Recommended production hardening

- Use strong, unique Supabase DB password.
- Rotate credentials if a secret was ever committed to git.
- Keep `APP_DEBUG=false`.
- Add Render health checks and alerts.
- Back up database regularly.
- Configure custom domain and HTTPS.

---

## 12) Minimal deploy checklist

- [ ] Code pushed to GitHub
- [ ] Supabase DB password confirmed/reset
- [ ] Render env vars set
- [ ] `APP_KEY` set
- [ ] `APP_DEBUG=false`
- [ ] Build and start commands configured
- [ ] First deploy successful
- [ ] Admin + intern smoke tests pass

---

If you want, the next step is adding a `render.yaml` blueprint so the service is reproducible from code.

