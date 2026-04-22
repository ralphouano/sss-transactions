#!/usr/bin/env sh
set -eu

if [ "${RESET_DB_ON_DEPLOY:-0}" = "1" ]; then
  echo "[startup] RESET_DB_ON_DEPLOY=1 detected. Running fresh migration (destructive)..."
  php artisan migrate:fresh --force
else
  echo "[startup] Running database migrations..."
  php artisan migrate --force
fi

echo "[startup] Seeding required roles/admin..."
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=AdminUserSeeder --force

echo "[startup] Running critical health checks..."
php artisan app:health-check

echo "[startup] Optimizing application..."
php artisan permission:cache-reset
php artisan optimize

if [ "${RUN_QUEUE_WORKER:-1}" = "1" ]; then
  echo "[startup] Starting queue worker in background..."
  php artisan queue:work --tries="${QUEUE_WORKER_TRIES:-3}" --timeout="${QUEUE_WORKER_TIMEOUT:-120}" --sleep="${QUEUE_WORKER_SLEEP:-3}" &
fi

echo "[startup] Starting web server..."
exec php -S 0.0.0.0:${PORT:-10000} -t public

