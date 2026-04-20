@echo off
echo Creating test users for SSS Transactions App...
echo.

php artisan tinker --execute="$admin = \App\Models\User::firstOrCreate(['email' => 'admin@sss.com'], ['name' => 'Admin User', 'password' => bcrypt('password123')]); if (!$admin->hasRole('admin')) { $admin->assignRole('admin'); } $intern1 = \App\Models\User::firstOrCreate(['email' => 'intern1@sss.com'], ['name' => 'John Intern', 'password' => bcrypt('password123'), 'intern_name' => 'John Doe']); if (!$intern1->hasRole('intern')) { $intern1->assignRole('intern'); } $intern2 = \App\Models\User::firstOrCreate(['email' => 'intern2@sss.com'], ['name' => 'Jane Intern', 'password' => bcrypt('password123'), 'intern_name' => 'Jane Smith']); if (!$intern2->hasRole('intern')) { $intern2->assignRole('intern'); } echo PHP_EOL . '✓ Admin: admin@sss.com / password123' . PHP_EOL . '✓ Intern 1: intern1@sss.com / password123' . PHP_EOL . '✓ Intern 2: intern2@sss.com / password123' . PHP_EOL;"

echo.
echo Done! You can now run: php artisan serve
pause
