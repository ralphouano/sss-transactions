# Troubleshooting Guide

## ✅ Fixed Issues

### "Target class [role] does not exist" Error
**Status**: FIXED ✅

**Problem**: The role middleware wasn't registered in Laravel 11+.

**Solution**: Already fixed in `bootstrap/app.php`. The middleware aliases are now properly registered:
- `role` → Spatie\Permission\Middleware\RoleMiddleware
- `permission` → Spatie\Permission\Middleware\PermissionMiddleware
- `role_or_permission` → Spatie\Permission\Middleware\RoleOrPermissionMiddleware

If you still see this error, run:
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

---

## Common Issues & Solutions

### 1. Server Won't Start (Port 8000 in use)

**Error**: "Address already in use"

**Solution**:
```bash
# Use a different port
php artisan serve --port=8001

# Or find and kill the process using port 8000
netstat -ano | findstr :8000
taskkill /PID [PID_NUMBER] /F
```

---

### 2. Database Errors

**Error**: "SQLSTATE[HY000]: General error: 1 no such table"

**Solution**:
```bash
# Run migrations
php artisan migrate:fresh

# Seed roles
php artisan db:seed --class=RoleSeeder

# Recreate users
CREATE_USERS.bat
```

---

### 3. Can't Login / User Doesn't Exist

**Error**: "These credentials do not match our records"

**Solution**:
```bash
# Run the user creation script
CREATE_USERS.bat

# Or create manually via tinker
php artisan tinker
```
Then:
```php
$admin = \App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@sss.com',
    'password' => bcrypt('password123')
]);
$admin->assignRole('admin');
exit
```

---

### 4. White Screen / 500 Error

**Possible Causes**:
- Assets not built
- Cache issues
- Permission errors

**Solution**:
```bash
# Build assets
npm run build

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# Check storage permissions (if on Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

---

### 5. Assets Not Loading / Vite Errors

**Error**: Mixed content, CORS, or 404 on assets

**Solution**:
```bash
# Rebuild assets
npm install
npm run build

# For development with hot reload
npm run dev
```

---

### 6. Signature Pad Not Working

**Issue**: Can't draw signatures

**Checklist**:
- ✅ Is JavaScript loaded? (Check browser console)
- ✅ Is SignaturePad component registered in `app.ts`?
- ✅ Are assets built? Run `npm run build`

**Solution**:
```bash
# Rebuild frontend
npm run build

# Clear browser cache
Ctrl + Shift + Delete (or Cmd + Shift + Delete on Mac)
```

---

### 7. Role/Permission Issues

**Error**: "User does not have the right roles"

**Solution**:
```bash
php artisan tinker
```
```php
// Check user's roles
$user = \App\Models\User::where('email', 'admin@sss.com')->first();
$user->getRoleNames(); // Should show ["admin"]

// Assign role if missing
$user->assignRole('admin');

// Or for intern
$intern = \App\Models\User::where('email', 'intern1@sss.com')->first();
$intern->assignRole('intern');
```

---

### 8. Routes Not Found (404)

**Error**: "404 Not Found" on `/intern/dashboard` or `/admin/reports`

**Solution**:
```bash
# Clear route cache
php artisan route:clear

# Verify routes exist
php artisan route:list --path=intern
php artisan route:list --path=admin

# Clear all caches
php artisan config:clear
php artisan cache:clear
```

---

### 9. CSV Export Not Working

**Issue**: Export button does nothing or errors

**Checklist**:
- ✅ Is `maatwebsite/excel` installed?
- ✅ Are there any transactions in the database?

**Solution**:
```bash
# Verify package is installed
composer show maatwebsite/excel

# If missing, install it
composer require maatwebsite/excel

# Create test transaction via intern dashboard first
```

---

### 10. Can't Access Admin Routes as Admin

**Error**: Redirected or "403 Forbidden"

**Solution**:
```bash
php artisan tinker
```
```php
// Verify admin role exists
\Spatie\Permission\Models\Role::all();

// If empty, run seeder
exit
```
```bash
php artisan db:seed --class=RoleSeeder
```

Then verify user has role:
```bash
php artisan tinker
```
```php
$admin = \App\Models\User::where('email', 'admin@sss.com')->first();
$admin->assignRole('admin');
exit
```

---

## Quick Fix Commands

### Full Reset (Nuclear Option)
```bash
# WARNING: This will delete all data!
php artisan migrate:fresh
php artisan db:seed --class=RoleSeeder
CREATE_USERS.bat
npm run build
php artisan serve
```

### Clear Everything (Safe)
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### Verify Installation
```bash
# Check routes
php artisan route:list

# Check database tables
php artisan tinker
\Schema::hasTable('users')
\Schema::hasTable('transactions')
\Schema::hasTable('roles')

# Check if users exist
\App\Models\User::count()

# Check if roles exist
\Spatie\Permission\Models\Role::count()
```

---

## Debug Mode

To see detailed errors, set in `.env`:
```env
APP_DEBUG=true
APP_ENV=local
```

Then restart the server.

---

## Getting Help

### Check Logs
```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# On Windows
type storage\logs\laravel.log
```

### Browser Console
Press `F12` in browser and check:
- Console tab (for JavaScript errors)
- Network tab (for failed requests)

### Verify Versions
```bash
php --version          # Should be 8.2+
composer --version
node --version         # Should be 18+
npm --version

php artisan --version  # Should be Laravel 12.x
```

---

## Still Having Issues?

1. Check `storage/logs/laravel.log` for detailed errors
2. Run `php artisan tinker` and test models directly
3. Verify all migrations ran: `php artisan migrate:status`
4. Check file permissions on `storage/` and `bootstrap/cache/`
5. Try the Full Reset commands above

---

**Last Updated**: April 15, 2026
