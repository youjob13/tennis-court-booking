# Quickstart Guide: Tennis Court Booking System

**Purpose**: Step-by-step instructions to set up and run the application  
**Date**: 2026-01-27  
**Prerequisites**: Docker Desktop, Git

## Overview

This guide walks through:
1. Project initialization with Laravel
2. Docker environment setup
3. Database migrations and seeding
4. Running the application
5. Accessing admin and user interfaces

**Estimated Time**: 30 minutes for first-time setup

---

## Step 1: Create Laravel Project

```bash
# Install Laravel via Composer (if not using Docker for this step)
composer create-project laravel/laravel booking-tennis
cd booking-tennis

# OR clone repository if project already exists
git clone <repository-url> booking-tennis
cd booking-tennis
```

---

## Step 2: Install Laravel Breeze

```bash
# Install Breeze for authentication scaffolding
composer require laravel/breeze --dev

# Install Breeze with Blade stack
php artisan breeze:install blade

# Install frontend dependencies
npm install
npm run build
```

---

## Step 3: Create Docker Configuration

### Create `Dockerfile` in project root:

```dockerfile
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

### Create `docker-compose.yml` in project root:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: booking-tennis-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
    networks:
      - booking-network
    depends_on:
      - db

  db:
    image: postgres:15-alpine
    container_name: booking-tennis-db
    restart: unless-stopped
    environment:
      POSTGRES_DB: ${DB_DATABASE:-booking_tennis}
      POSTGRES_USER: ${DB_USERNAME:-postgres}
      POSTGRES_PASSWORD: ${DB_PASSWORD:-secret}
    volumes:
      - pgdata:/var/lib/postgresql/data
    ports:
      - "5432:5432"
    networks:
      - booking-network

  nginx:
    image: nginx:alpine
    container_name: booking-tennis-nginx
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx/nginx.conf:/etc/nginx/conf.d/default.conf
    networks:
      - booking-network
    depends_on:
      - app

networks:
  booking-network:
    driver: bridge

volumes:
  pgdata:
    driver: local
```

### Create `docker/nginx/nginx.conf`:

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Step 4: Configure Environment

### Copy `.env.example` to `.env`:

```bash
cp .env.example .env
```

### Update `.env` with PostgreSQL configuration:

```env
APP_NAME="Tennis Court Booking"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=booking_tennis
DB_USERNAME=postgres
DB_PASSWORD=secret

SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

---

## Step 5: Start Docker Containers

```bash
# Create docker/nginx directory
mkdir -p docker/nginx

# Copy nginx config (see above)

# Build and start containers
docker-compose up -d --build

# Check containers are running
docker-compose ps

# Expected output:
# NAME                    STATUS    PORTS
# booking-tennis-app      Up        9000/tcp
# booking-tennis-db       Up        0.0.0.0:5432->5432/tcp
# booking-tennis-nginx    Up        0.0.0.0:8000->80/tcp
```

---

## Step 6: Initialize Laravel Application

```bash
# Access app container
docker-compose exec app bash

# Inside container:

# Install Composer dependencies (if not done)
composer install

# Generate application key
php artisan key:generate

# Create storage symlink for court photos
php artisan storage:link

# Exit container
exit
```

---

## Step 7: Create Database Migrations

### Create migrations directory structure:
```bash
docker-compose exec app php artisan make:migration create_users_table
docker-compose exec app php artisan make:migration create_courts_table
docker-compose exec app php artisan make:migration create_bookings_table
```

### Migration files already created (see data-model.md for schema details)

---

## Step 8: Run Migrations

```bash
# Run all migrations
docker-compose exec app php artisan migrate

# Expected output:
# Migrating: 2026_01_27_000001_create_users_table
# Migrated:  2026_01_27_000001_create_users_table (XX.XXms)
# Migrating: 2026_01_27_000002_create_courts_table
# Migrated:  2026_01_27_000002_create_courts_table (XX.XXms)
# Migrating: 2026_01_27_000003_create_bookings_table
# Migrated:  2026_01_27_000003_create_bookings_table (XX.XXms)
```

---

## Step 9: Seed Database with Initial Data

### Create seeders:
```bash
docker-compose exec app php artisan make:seeder CourtSeeder
docker-compose exec app php artisan make:seeder UserSeeder
```

### Run seeders (see data-model.md for seeder code):

```bash
# Seed database
docker-compose exec app php artisan db:seed

# This creates:
# - Admin user: admin@example.com / password
# - Test user: user@example.com / password
# - 5-10 sample courts with varying prices
```

---

## Step 10: Access the Application

### User Interface:
- **URL**: http://localhost:8000
- **Test User**: user@example.com / password
- **Features**:
  - Browse courts on main page
  - View court details
  - Book time slots
  - Complete dummy payment

### Admin Interface:
- **URL**: http://localhost:8000/admin
- **Admin User**: admin@example.com / password
- **Features**:
  - View dashboard statistics
  - Add/disable/enable courts
  - Remove courts (if no future bookings)
  - Cancel locked bookings

---

## Development Workflow

### Common Commands:

```bash
# View logs
docker-compose logs -f app
docker-compose logs -f nginx

# Stop containers
docker-compose down

# Start containers (after first build)
docker-compose up -d

# Restart single service
docker-compose restart app

# Access database directly
docker-compose exec db psql -U postgres -d booking_tennis

# Run artisan commands
docker-compose exec app php artisan <command>

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear

# Re-run migrations (fresh database)
docker-compose exec app php artisan migrate:fresh --seed
```

---

## Creating Admin User Manually

If you need to promote an existing user to admin:

```bash
# Access database
docker-compose exec db psql -U postgres -d booking_tennis

# Update user role
UPDATE users SET role = 'admin' WHERE email = 'your@email.com';

# Exit
\q
```

---

## Troubleshooting

### Issue: "Connection refused" to database

**Solution**:
```bash
# Check db container is running
docker-compose ps

# Restart database
docker-compose restart db

# Check .env DB_HOST is set to "db" (container name)
```

### Issue: "Permission denied" errors

**Solution**:
```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Issue: "SQLSTATE[08006] Connection failure"

**Solution**:
```bash
# Database may not be ready, wait 10 seconds and retry
docker-compose down
docker-compose up -d
sleep 10
docker-compose exec app php artisan migrate
```

### Issue: Styles not loading

**Solution**:
```bash
# Rebuild frontend assets
npm run build

# Restart nginx
docker-compose restart nginx
```

---

## Project Structure After Setup

```
booking-tennis/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CourtController.php
│   │   │   ├── BookingController.php
│   │   │   └── Admin/
│   │   └── Middleware/
│   │       └── IsAdmin.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Court.php
│   │   └── Booking.php
│   └── Services/
│       ├── BookingLockService.php
│       └── PaymentService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── courts/
│       ├── bookings/
│       └── admin/
├── routes/
│   ├── web.php
│   └── admin.php
├── docker/
│   └── nginx/
│       └── nginx.conf
├── docker-compose.yml
├── Dockerfile
└── .env
```

---

## Next Steps

After successful setup:

1. **Review Routes**: Check [contracts/routes.md](contracts/routes.md) for all available routes
2. **Implement Controllers**: Create controllers based on route specifications
3. **Build Views**: Design Blade templates with Tailwind CSS
4. **Test Booking Flow**: Verify lock mechanism and payment processing
5. **Customize Courts**: Add real court data via admin interface

---

## Production Deployment Checklist

- [ ] Change `APP_DEBUG=false` in `.env`
- [ ] Set strong `APP_KEY`
- [ ] Use production database credentials
- [ ] Configure real payment gateway
- [ ] Set up HTTPS/SSL certificates
- [ ] Configure queue worker for background jobs
- [ ] Set up backup strategy for PostgreSQL
- [ ] Configure logging and monitoring
- [ ] Review and optimize database indexes
- [ ] Implement rate limiting for API endpoints

---

**Status**: Quickstart guide complete. Ready for implementation.
