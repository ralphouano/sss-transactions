# SSS Transactions App - Setup & Run Guide

## Prerequisites
- PHP 8.2+ with extensions: pdo, sqlite, mbstring, openssl, tokenizer, ctype, json
- Composer
- Node.js 18+ and npm
- A web server (built-in PHP server works fine for development)

## Initial Setup (Already Done)

The app has been fully set up with:
- ✅ Laravel 12 with Breeze (Vue + TypeScript)
- ✅ Database tables and migrations
- ✅ All models, controllers, and routes
- ✅ Signature pad component
- ✅ Admin and Intern dashboards
- ✅ shadcn-vue UI components
- ✅ Assets built

## How to Run the Application

### Step 1: Start the Development Server

Open a terminal in the project directory and run:

```bash
php artisan serve
```

This will start the server at: **http://127.0.0.1:8000**

### Step 2: (Optional) Run Vite Dev Server for Hot Reload

If you want to make changes to the frontend and see them live, open a **second terminal** and run:

```bash
npm run dev
```

This enables hot module replacement for Vue components.

## Creating Test Users

You need to create users with roles to test the application. Use Laravel Tinker:

```bash
php artisan tinker
```

### Create an Admin User:

```php
$admin = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@sss.com',
    'password' => bcrypt('password123')
]);
$admin->assignRole('admin');
```

### Create Intern Users:

```php
$intern1 = \App\Models\User::create([
    'name' => 'John Intern',
    'email' => 'intern1@sss.com',
    'password' => bcrypt('password123'),
    'intern_name' => 'John Doe'
]);
$intern1->assignRole('intern');

$intern2 = \App\Models\User::create([
    'name' => 'Jane Intern',
    'email' => 'intern2@sss.com',
    'password' => bcrypt('password123'),
    'intern_name' => 'Jane Smith'
]);
$intern2->assignRole('intern');

exit
```

## Application Routes

### Public Routes
- **Home**: http://127.0.0.1:8000
- **Login**: http://127.0.0.1:8000/login
- **Register**: http://127.0.0.1:8000/register

### Intern Routes (require 'intern' role)
- **Dashboard**: http://127.0.0.1:8000/intern/dashboard
  - Record transactions with signature pad
  - Select intern, member name, transaction types
  - Draw and save signatures

### Admin Routes (require 'admin' role)
- **Manage Interns**: http://127.0.0.1:8000/admin/interns
  - View all interns
  - Edit intern names
  
- **Reports**: http://127.0.0.1:8000/admin/reports
  - View transactions by period (daily/weekly/monthly)
  - Export to CSV
  - See signature thumbnails

## Testing the Application

### As an Intern:
1. Login as `intern1@sss.com` / `password123`
2. Go to Intern Dashboard
3. Select an intern from dropdown
4. Enter member name (e.g., "Juan dela Cruz")
5. Check transaction types (e.g., Loan, Pension)
6. Draw a signature in the signature pad
7. Click "Record Transaction"
8. Verify success message appears

### As an Admin:
1. Login as `admin@sss.com` / `password123`
2. **Manage Interns**:
   - Click "Manage Interns" 
   - Edit intern names
3. **View Reports**:
   - Click "Reports"
   - Switch between Daily/Weekly/Monthly tabs
   - View transaction details and signatures
   - Click "Export CSV" to download

## Database

The app uses **SQLite** by default (file: `database/database.sqlite`).

To switch to MySQL:
1. Update `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sss_transactions
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```
2. Create the database in MySQL
3. Run: `php artisan migrate:fresh --seed`

## Available Commands

### Development
```bash
# Start Laravel server
php artisan serve

# Start Vite dev server (hot reload)
npm run dev

# Build assets for production
npm run build
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Seed roles
php artisan db:seed --class=RoleSeeder

# Open Tinker (Laravel REPL)
php artisan tinker
```

### Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Transaction Types Available

When recording transactions, you can select from:
- Loan
- Pension
- Disability
- Death
- Retirement
- Sickness
- Maternity

## Features Implemented

✅ **Authentication & Authorization**
- Laravel Breeze with Inertia + Vue
- Spatie Laravel Permission (role-based access)
- Admin and Intern roles

✅ **Signature Capture**
- HTML5 Canvas-based signature pad
- Base64 encoding for storage
- Mobile touch support
- Clear/undo functionality

✅ **Intern Dashboard**
- Select intern from dropdown
- Record member transactions
- Multiple transaction type selection
- Signature capture and validation

✅ **Admin Features**
- Manage intern names
- View transactions by period
- Filter: Daily, Weekly, Monthly
- Export to CSV with all data
- View signature thumbnails

✅ **UI/UX**
- shadcn-vue components (beautiful, accessible)
- Responsive design
- Form validation
- Success/error notifications
- Modern, clean interface

## Troubleshooting

### Port 8000 already in use
```bash
php artisan serve --port=8001
```

### Assets not loading
```bash
npm run build
```

### Database errors
```bash
php artisan migrate:fresh
php artisan db:seed --class=RoleSeeder
```

### Permission errors
Make sure these directories are writable:
- `storage/`
- `bootstrap/cache/`

```bash
chmod -R 775 storage bootstrap/cache
```

## Project Structure

```
sss-transactions/
├── app/
│   ├── Http/Controllers/
│   │   ├── InternController.php          # Intern dashboard & recording
│   │   ├── ReportController.php          # Reports & exports
│   │   └── InternManagementController.php # Admin intern management
│   ├── Models/
│   │   ├── User.php                       # User with roles
│   │   └── Transaction.php                # Transaction model
│   └── Exports/
│       └── TransactionsExport.php         # CSV export logic
├── database/
│   ├── migrations/                        # All database tables
│   └── seeders/
│       └── RoleSeeder.php                 # Creates admin/intern roles
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   ├── SignaturePad.vue          # Signature component
│   │   │   └── ui/                        # shadcn-vue components
│   │   ├── Pages/
│   │   │   ├── Intern/
│   │   │   │   └── Dashboard.vue         # Intern dashboard
│   │   │   └── Admin/
│   │   │       ├── Interns.vue           # Manage interns
│   │   │       └── Reports.vue           # View reports
│   │   └── Layouts/
│   │       └── AuthenticatedLayout.vue   # Main layout
│   └── css/
│       └── app.css                        # Tailwind styles
└── routes/
    └── web.php                            # All routes with role middleware
```

## Next Steps (Optional Enhancements)

While the core app is fully functional, you can add:

1. **Prettier Theme**: Customize SSS blue color in `tailwind.config.js`
2. **Client Validation**: Add Zod schemas for form validation
3. **User Management**: Add ability to create/delete users from admin panel
4. **Advanced Reports**: Add charts, analytics, date range filters
5. **Email Notifications**: Send confirmations to members
6. **Print Receipts**: Generate PDF receipts for transactions
7. **Audit Logs**: Track who made what changes

## Support

For issues or questions about this application, refer to:
- Laravel docs: https://laravel.com/docs
- Inertia.js: https://inertiajs.com
- shadcn-vue: https://shadcn-vue.com

---

**Built with:** Laravel 12, Vue 3, TypeScript, Inertia.js, Tailwind CSS, shadcn-vue
