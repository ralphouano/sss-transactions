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
php artisan tinker --execute="try { $admin = \App\Models\User::firstOrCreate(['email' => 'admin@sss.com'], ['name' => 'Admin User', 'password' => bcrypt('password123')]); if (!$admin->hasRole('admin')) $admin->assignRole('admin'); $intern1 = \App\Models\User::firstOrCreate(['email' => 'intern1@sss.com'], ['name' => 'John Intern', 'password' => bcrypt('password123'), 'intern_name' => 'John Doe']); if (!$intern1->hasRole('intern')) $intern1->assignRole('intern'); $intern2 = \App\Models\User::firstOrCreate(['email' => 'intern2@sss.com'], ['name' => 'Jane Intern', 'password' => bcrypt('password123'), 'intern_name' => 'Jane Smith']); if (!$intern2->hasRole('intern')) $intern2->assignRole('intern'); echo '\n✓ Users ready!\n'; } catch (\Exception $e) { echo 'Users already exist or error: ' . $e->getMessage(); }" 2>nul

echo.
echo ============================================
echo   Ready to Go!
echo ============================================
echo   Admin: admin@sss.com / password123
echo   Intern 1: intern1@sss.com / password123
echo   Intern 2: intern2@sss.com / password123
echo ============================================
echo.
echo [3/3] Starting Laravel development server...
echo.
echo Visit: http://127.0.0.1:8000
echo Press Ctrl+C to stop the server
echo.

php artisan serve
