@echo off
echo ============================================
echo   SSS Transactions App - Quick Start
echo ============================================
echo.

echo [1/3] Clearing caches...
php artisan config:clear >nul 2>&1
php artisan route:clear >nul 2>&1
php artisan cache:clear >nul 2>&1
echo    ✓ Caches cleared

echo.
echo [2/3] Creating test users...
php artisan tinker --execute="try { if (!app()->environment('local')) { throw new \RuntimeException('Refusing demo user creation outside local environment.'); } $adminPass = \Illuminate\Support\Str::password(14); $intern1Pass = \Illuminate\Support\Str::password(14); $intern2Pass = \Illuminate\Support\Str::password(14); $admin = \App\Models\User::updateOrCreate(['email' => 'admin@local.invalid'], ['name' => 'Admin User', 'password' => bcrypt($adminPass)]); if (!$admin->hasRole('admin')) $admin->assignRole('admin'); $intern1 = \App\Models\User::updateOrCreate(['email' => 'intern1@local.invalid'], ['name' => 'John Intern', 'password' => bcrypt($intern1Pass), 'intern_name' => 'John Doe']); if (!$intern1->hasRole('intern')) $intern1->assignRole('intern'); $intern2 = \App\Models\User::updateOrCreate(['email' => 'intern2@local.invalid'], ['name' => 'Jane Intern', 'password' => bcrypt($intern2Pass), 'intern_name' => 'Jane Smith']); if (!$intern2->hasRole('intern')) $intern2->assignRole('intern'); echo '\nLOCAL DEMO CREDENTIALS (save now):\n'; echo 'Admin: admin@local.invalid / ' . $adminPass . '\n'; echo 'Intern 1: intern1@local.invalid / ' . $intern1Pass . '\n'; echo 'Intern 2: intern2@local.invalid / ' . $intern2Pass . '\n'; } catch (\Exception $e) { echo 'Skipped demo user creation: ' . $e->getMessage(); }" 2>nul

echo.
echo ============================================
echo   Ready to Go!
echo ============================================
echo   Demo credentials are printed above (local only)
echo ============================================
echo.
echo [3/3] Starting Laravel development server...
echo.
echo Visit: http://127.0.0.1:8000
echo Press Ctrl+C to stop the server
echo.

php artisan serve
