# Tennis Court Booking System

A Laravel-based web application for managing tennis court bookings with real-time availability, payment integration, and admin management features.

## Features

- **Browse Courts**: View all available tennis courts with real-time availability indicators
- **Booking System**: Reserve courts with 10-minute payment lock mechanism to prevent double-booking
- **Payment Flow**: Integrated dummy payment service with countdown timer and lock expiration
- **User Authentication**: Register with optional phone field, login/logout, session management
- **Admin Panel**: Dedicated dashboard for managing courts and bookings (create/disable/enable/delete courts, view/cancel locked bookings)
- **Responsive Design**: Fully mobile-responsive using Tailwind CSS

## Tech Stack

- **Backend**: Laravel 11.x with PHP 8.2+
- **Frontend**: Blade templates, Tailwind CSS, Alpine.js (via Breeze)
- **Database**: PostgreSQL 15+
- **Authentication**: Laravel Breeze (Blade stack)
- **Containerization**: Docker with docker-compose (nginx, PostgreSQL)
- **Code Quality**: Laravel Pint (PSR-12)

## Quick Start

### Prerequisites
- Docker and Docker Compose installed
- PHP 8.2+ (for local development)
- Composer
- Node.js and npm

### Installation

1. **Clone and setup environment**:
```bash
git clone <repository-url>
cd booking-tennis
cp .env.example .env
```

2. **Start Docker containers**:
```bash
docker-compose up -d
```

3. **Install dependencies and setup application**:
```bash
docker-compose exec app composer install
docker-compose exec app npm install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

4. **Build frontend assets**:
```bash
docker-compose exec app npm run build
```

5. **Access application**:
- Main site: http://localhost:8000
- Admin panel: http://localhost:8000/admin (login as admin)

6. **Test accounts** (created by seeders):
   - Admin: admin@tennis.com / password
   - User 1: john@example.com / password
   - User 2: jane@example.com / password

## Project Structure

```
app/
├── Http/Controllers/
│   ├── CourtController.php         # Public court listing and details
│   ├── BookingController.php       # Booking flow and payment
│   └── Admin/                      # Admin-only controllers
│       ├── DashboardController.php
│       ├── CourtController.php
│       └── BookingController.php
├── Models/                         # Eloquent models (User, Court, Booking)
├── Services/                       # Business logic layer
│   ├── AvailabilityService.php    # Court availability calculation
│   ├── BookingLockService.php     # Lock mechanism with SELECT FOR UPDATE
│   └── DummyPaymentService.php    # Payment simulation
└── Console/Commands/
    └── UnlockExpiredBookings.php  # Scheduled task

database/migrations/                # Database schema
resources/
├── views/                          # Blade templates
│   ├── courts/                    # Court browsing views
│   ├── bookings/                  # Booking flow views
│   ├── admin/                     # Admin panel views
│   ├── auth/                      # Authentication views
│   └── errors/                    # Custom error pages (404/403/500)
└── css/app.css                    # Tailwind CSS

routes/web.php                      # HTTP routes (public, auth, admin)
```

## Key Features Explained

### Booking Flow
1. User browses courts and sees real-time availability
2. Selects time slot → system creates locked booking (10-min expiration)
3. Payment page shows countdown timer
4. On successful payment → booking confirmed
5. On lock expiration → booking cancelled, slot released

### Admin Features
- View dashboard with statistics (courts, bookings, revenue)
- Create new courts with operating hours and pricing
- Disable/enable courts (prevents new bookings)
- Delete courts (only if no future confirmed bookings)
- View all locked bookings and cancel if needed
- Cannot cancel confirmed bookings (business rule)

### Lock Mechanism
- 10-minute payment window per booking
- Database-level locking with SELECT FOR UPDATE
- Automatic unlock after 30 seconds if initial lock acquisition fails
- Scheduled command runs every minute to clean expired locks

## Testing

### Test Accounts
| Email              | Password | Role  | Use Case                    |
|--------------------|----------|-------|-----------------------------|
| admin@tennis.com   | password | admin | Access admin panel          |
| john@example.com   | password | user  | Regular user booking        |
| jane@example.com   | password | user  | Concurrent booking testing  |

### Test Payment
- **Success**: Enter amount > 0 and click "Process Payment"
- **Failure**: Enter amount 0 or leave empty

### Manual Testing Checklist
- [ ] **US1**: Browse courts and see availability indicators
- [ ] **US2**: Complete booking flow (lock → payment → confirmation)
- [ ] **US3**: Register with phone, login, logout
- [ ] **US4**: Admin dashboard, create court, disable court, cancel locked booking

## Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f app

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# Access PostgreSQL
docker-compose exec db psql -U postgres -d tennis_booking

# Restart application
docker-compose restart app
```

## Development

### Code Formatting
```bash
./vendor/bin/pint          # Format all files
./vendor/bin/pint --dirty  # Format only changed files
```

### Database
- **Migrations**: Run `php artisan migrate` to create tables
- **Seeders**: Run `php artisan db:seed` to populate test data (2 courts, 3 users)
- **Fresh start**: Run `php artisan migrate:fresh --seed`

### Scheduled Tasks
The `unlock:expired-bookings` command runs every minute via Laravel scheduler:
```bash
# Run manually
php artisan unlock:expired-bookings

# Start scheduler (in production)
php artisan schedule:work
```

## API Endpoints

### Public Routes
- `GET /` - Welcome page
- `GET /courts` - Court listing with availability
- `GET /courts/{id}` - Court details

### Authenticated Routes
- `POST /bookings` - Create new booking (locks slot)
- `GET /bookings/{id}/payment` - Payment page
- `POST /bookings/{id}/payment` - Process payment

### Admin Routes (auth + admin middleware)
- `GET /admin` - Dashboard with statistics
- `GET /admin/courts` - Manage courts
- `POST /admin/courts` - Create new court
- `PATCH /admin/courts/{id}/disable` - Disable court
- `PATCH /admin/courts/{id}/enable` - Enable court
- `DELETE /admin/courts/{id}` - Delete court
- `GET /admin/bookings` - View locked bookings
- `DELETE /admin/bookings/{id}` - Cancel locked booking

## Troubleshooting

### Port 8000 already in use
```bash
# Find and kill process
lsof -ti:8000 | xargs kill -9   # macOS/Linux
netstat -ano | findstr :8000    # Windows
```

### Database connection issues
- Verify PostgreSQL container is running: `docker-compose ps`
- Check .env database credentials match docker-compose.yml
- Restart database: `docker-compose restart db`

### Permission errors
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## License

This project is open-source software licensed under the MIT license.

## Support

For issues or questions, refer to the specifications in `specs/001-tennis-court-booking/`.
