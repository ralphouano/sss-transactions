@echo off
echo Creating LOCAL-ONLY demo users for SSS Transactions App...
echo.

php artisan tinker --execute="if (!app()->environment('local')) { throw new \RuntimeException('Refusing to create demo users outside local environment.'); } $adminPass = \Illuminate\Support\Str::password(14); $intern1Pass = \Illuminate\Support\Str::password(14); $intern2Pass = \Illuminate\Support\Str::password(14); $admin = \App\Models\User::updateOrCreate(['email' => 'admin@local.invalid'], ['name' => 'Admin User', 'password' => bcrypt($adminPass)]); if (!$admin->hasRole('admin')) { $admin->assignRole('admin'); } $intern1 = \App\Models\User::updateOrCreate(['email' => 'intern1@local.invalid'], ['name' => 'John Intern', 'password' => bcrypt($intern1Pass), 'intern_name' => 'John Doe']); if (!$intern1->hasRole('intern')) { $intern1->assignRole('intern'); } $intern2 = \App\Models\User::updateOrCreate(['email' => 'intern2@local.invalid'], ['name' => 'Jane Intern', 'password' => bcrypt($intern2Pass), 'intern_name' => 'Jane Smith']); if (!$intern2->hasRole('intern')) { $intern2->assignRole('intern'); } echo PHP_EOL . 'LOCAL DEMO CREDENTIALS (save now):' . PHP_EOL . 'Admin: admin@local.invalid / ' . $adminPass . PHP_EOL . 'Intern 1: intern1@local.invalid / ' . $intern1Pass . PHP_EOL . 'Intern 2: intern2@local.invalid / ' . $intern2Pass . PHP_EOL;"

echo.
echo Done! You can now run: php artisan serve
pause
