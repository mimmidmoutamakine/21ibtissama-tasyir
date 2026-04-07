# 21 Ibtissama

Laravel application for managing the operational workflow of the Moroccan association "21 Ibtissama".

## Stack

- Laravel 13
- PHP 8.3
- SQLite locally / MySQL in production
- Vite
- Spatie Permission
- Spatie Activitylog

## Local Run

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

## Production Notes

- Use `.env.hostinger.example` as the base for production environment values.
- The root `.htaccess` redirects traffic to `public/`, which helps when the project is deployed under `public_html`.
- Run these commands after deployment:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm install
npm run build
```

## Initial Accounts

Initial employee/admin credentials are generated locally into:

`storage/app/private/initial_credentials.json`

This file is intentionally ignored from Git and is not pushed to GitHub.
