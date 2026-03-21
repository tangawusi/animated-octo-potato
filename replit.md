# AJE Notes - Symfony + React Notes Service

## Overview
A Symfony 5.4 backend + React 18 frontend single-page application (SPA) for managing notes. Users can register, confirm accounts, log in, and manage notes with filtering by title, content, status, and category.

## Tech Stack
- **Backend:** PHP 8.2 / Symfony 5.4
- **Frontend:** React 18 + TypeScript, bundled with Webpack Encore (Yarn)
- **Database:** PostgreSQL (Replit built-in)
- **ORM:** Doctrine ORM with Migrations

## Project Structure
- `src/` - PHP backend (Controllers, Entities, Repositories, Security, Services)
- `assets/` - React/TypeScript frontend source
- `public/` - Web root (compiled assets in `public/build/`)
- `templates/` - Twig templates (single `index.html.twig` SPA host)
- `config/` - Symfony configuration
- `migrations/` - Doctrine database migrations

## Running the Application
The app runs on port 5000 using the PHP built-in server:
```
php -S 0.0.0.0:5000 -t public public/router.php
```
`public/router.php` handles static file serving; dynamic requests go through `public/index.php` (Symfony kernel).

## Environment Setup
- `APP_ENV=dev` - Symfony environment
- `APP_SECRET` - Symfony secret key
- `DATABASE_URL` - PostgreSQL connection string (auto-provided by Replit)
- `MESSENGER_TRANSPORT_DSN=doctrine://default`
- `MAILER_DSN=null://null` (emails are written to `var/emails/`)

## Development Commands
- `composer install` - Install PHP dependencies
- `yarn install` - Install JS dependencies  
- `yarn dev` - Build frontend assets (development)
- `yarn build` - Build frontend assets (production)
- `php bin/console doctrine:migrations:migrate` - Run DB migrations
- `php bin/console cache:clear` - Clear Symfony cache

## Database
- PostgreSQL (Replit built-in), configured via `DATABASE_URL` secret
- Migration adapted from MySQL to PostgreSQL syntax
- Tables: `app_user`, `note`

## Key Adaptations for Replit
- Database changed from MySQL to Replit's built-in PostgreSQL
- Migration file updated to PostgreSQL syntax (SERIAL, BOOLEAN, TEXT instead of MySQL types)
- `doctrine.yaml` configured with `server_version: '15'` for PostgreSQL
- `public/router.php` added to correctly serve static assets with PHP built-in server
- Frontend assets pre-compiled; app served via `php -S`
