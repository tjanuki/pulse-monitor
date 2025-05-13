# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel application that appears to be set up as a pulse-monitoring system. The application follows Laravel's standard MVC architecture and uses SQLite as the default database.

## Common Commands

### Setup

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Create database tables
php artisan migrate

# Seed the database with test data
php artisan db:seed
```

### Development

```bash
# Start the development server
php artisan serve

# Run Vite dev server for assets
npm run dev

# Build assets for production
npm run build

# Run all development services (server, queue listener, logs, vite)
composer dev
```

### Testing

```bash
# Run all tests
php artisan test
# or
composer test

# Run a specific test
php artisan test --filter=TestName
```

### Linting

```bash
# Run Laravel Pint (PHP code style fixer)
./vendor/bin/pint
```

## Architecture

This application follows Laravel's standard architecture:

- **Models**: Located in `app/Models/`, define database relationships and business logic
- **Controllers**: Located in `app/Http/Controllers/`, handle HTTP requests and responses
- **Routes**: Located in `routes/`, define URL patterns and map them to controllers
- **Views**: Located in `resources/views/`, contain templates rendered by controllers
- **Migrations**: Located in `database/migrations/`, define database schema changes
- **Factories & Seeders**: Located in `database/factories/` and `database/seeders/`, generate test data

The application uses:
- SQLite as the default database
- Laravel's built-in authentication system
- Tailwind CSS for styling
- Vite for asset compilation