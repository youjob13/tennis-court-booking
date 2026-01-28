# Research: Tennis Court Booking System

**Purpose**: Document technical decisions and alternatives for Laravel-based booking system  
**Date**: 2026-01-27  
**Status**: Complete

## Research Areas

### 1. Laravel Authentication & Authorization

**Decision**: Laravel Breeze with custom role-based access control

**Rationale**:
- Laravel Breeze provides lightweight, PSR-12 compliant authentication scaffolding
- Includes registration, login, password reset out of the box
- Generates Blade views with Tailwind CSS (matches UI requirements)
- Minimal dependencies compared to Laravel Jetstream or Fortify
- Easy to extend with role-based access via middleware

**Alternatives Considered**:
- **Laravel Jetstream**: Too feature-rich for MVP (includes teams, 2FA, API tokens)
- **Custom auth from scratch**: Violates "avoid reinventing the wheel" principle
- **Laravel Fortify**: Headless authentication requires more frontend work

**Implementation Approach**:
- Install Laravel Breeze via Composer
- Add `role` column to users table migration (enum: 'user', 'admin')
- Create `IsAdmin` middleware to guard admin routes
- Use Laravel's policy system if more granular permissions needed later

---

### 2. Docker Compose Setup

**Decision**: Three-container setup (app, database, nginx)

**Rationale**:
- **App container**: PHP 8.2-FPM with Laravel dependencies
- **Database container**: PostgreSQL 15 official image
- **Nginx container**: Serves Laravel public directory, proxies PHP requests
- Separate containers provide isolation and match production deployment patterns
- Volume mounts for live code reloading during development

**Alternatives Considered**:
- **Single container with supervisor**: Less maintainable, harder to scale
- **Laravel Sail**: Opinionated setup with many unused services for this MVP
- **Apache instead of Nginx**: Nginx has better performance and simpler config for PHP-FPM

**Implementation Approach**:
```yaml
services:
  app:
    build: .
    volumes:
      - .:/var/www
    env_file: .env
  
  db:
    image: postgres:15
    environment:
      POSTGRES_DB, POSTGRES_USER, POSTGRES_PASSWORD from .env
    volumes:
      - pgdata:/var/lib/postgresql/data
  
  nginx:
    image: nginx:alpine
    ports:
      - "8000:80"
    volumes:
      - ./docker/nginx.conf
      - ./public (Laravel)
```

---

### 3. PostgreSQL Schema Design

**Decision**: Five core tables with foreign keys and indexes

**Rationale**:
- **users**: Laravel's default schema + `role` enum column
- **courts**: name, description, photo_url, hourly_price, status (active/disabled), operating_hours (JSON)
- **bookings**: court_id, user_id, start_datetime, duration_hours, total_price, status (locked/confirmed), payment_reference, lock_expires_at
- **time_slots**: Derived/calculated from bookings (not a separate table - calculated on demand)
- **roles table**: NOT needed - simple enum on users table suffices for MVP

**Alternatives Considered**:
- **Separate roles table with pivot**: Over-engineering for binary admin/user distinction
- **Time slots as pre-generated records**: Wasteful storage, complex maintenance
- **NoSQL/Document store**: PostgreSQL JSON columns provide flexibility without losing ACID guarantees

**Key Design Decisions**:
- Use PostgreSQL `TIMESTAMPTZ` for all datetime fields (timezone-aware)
- `lock_expires_at` nullable timestamp - NULL means booking confirmed
- Unique index on `(court_id, start_datetime)` for each booking to prevent double-booking
- Use database-level check constraints for status enum values
- Foreign keys with CASCADE on user/court deletion (or RESTRICT for data integrity)

---

### 4. Booking Lock Mechanism

**Decision**: Database-level atomic locking with pessimistic concurrency control

**Rationale**:
- PostgreSQL transactions with `SELECT FOR UPDATE` ensure atomic lock acquisition
- Race condition handling: First transaction to acquire lock succeeds, others fail immediately
- Lock expiration handled by scheduled Laravel command (runs every minute, releases expired locks)
- 30-second payment failure delay implemented with `unlocked_after` timestamp column

**Alternatives Considered**:
- **Redis for distributed locks**: Over-engineering for MVP with single app instance
- **Optimistic concurrency (version numbers)**: Requires more complex retry logic
- **Application-level mutexes**: Doesn't work across multiple Laravel workers/containers

**Implementation Approach**:
```php
// BookingLockService.php
DB::transaction(function () {
    $booking = Booking::lockForUpdate()
        ->where('court_id', $courtId)
        ->where('start_datetime', $startTime)
        ->where('status', 'available')
        ->first();
    
    if (!$booking) {
        throw new SlotUnavailableException();
    }
    
    $booking->status = 'locked';
    $booking->user_id = $userId;
    $booking->lock_expires_at = now()->addMinutes(10);
    $booking->save();
});
```

---

### 5. Payment Gateway Integration

**Decision**: Deferred to implementation phase - interface defined in research

**Rationale**:
- MVP can use dummy payment service for testing
- Common options: Stripe, PayPal, local payment processors
- Interface abstraction allows swapping providers without changing business logic

**Recommended Approach**:
- Create `PaymentService` interface with `charge()`, `refund()`, `getStatus()` methods
- Implement `DummyPaymentService` for MVP (always succeeds after 2-second delay)
- Store payment gateway reference in `bookings.payment_reference` column
- Handle webhooks for async payment confirmations (out of MVP scope)

**Integration Requirements**:
- Payment service must return success/failure synchronously for MVP
- Store payment reference for refund capability (even though refunds out of scope initially)
- Log all payment attempts for debugging

---

### 6. Tailwind CSS & Blade Templates

**Decision**: Tailwind CSS 3.x with Laravel Blade server-side rendering

**Rationale**:
- Tailwind provides utility-first CSS matching "consistent UX patterns" principle
- No build complexity beyond Laravel Mix/Vite (included with Laravel)
- Mobile-responsive utilities built-in (`sm:`, `md:`, `lg:` breakpoints)
- Blade templates keep logic server-side (simpler than SPA for MVP)

**Alternatives Considered**:
- **Bootstrap**: More opinionated, harder to customize consistently
- **Vue.js/React SPA**: Over-engineering for MVP, violates simplicity principle
- **Custom CSS**: Would take longer and risk inconsistency

**Implementation Approach**:
- Use Laravel Vite integration (default in Laravel 11)
- Shared `layouts/app.blade.php` with navigation, header, footer
- Component-based Blade includes for reusable elements (court card, booking form)
- Tailwind forms plugin for consistent form styling

---

### 7. Database Migrations & Seeding

**Decision**: Laravel migrations with separate seeders for dev data

**Rationale**:
- Migrations keep schema version-controlled and deployable
- Seeders provide initial data (roles, sample courts) for development
- Use Faker library for generating realistic test data
- Production deployments run migrations only, not seeders

**Migration Strategy**:
1. `create_users_table` - Laravel default + role column
2. `create_courts_table` - name, description, photo_url, status, pricing
3. `create_bookings_table` - booking details, locks, payment status
4. Foreign key constraints defined in migrations

**Seeding Strategy**:
- `RoleSeeder`: Not needed (roles are enum values)
- `CourtSeeder`: 5-10 sample courts with photos from placeholder service
- `UserSeeder`: Admin user (email: admin@example.com, password: password)
- Development only - production data entered via admin UI

---

## Summary of Key Decisions

| Area | Decision | Rationale |
|------|----------|-----------|
| Authentication | Laravel Breeze + role enum | Lightweight, includes Tailwind UI |
| Containerization | Docker Compose (app/db/nginx) | Standard Laravel deployment pattern |
| Database | PostgreSQL 15 with 3 core tables | ACID compliance, JSON support |
| Lock Mechanism | PostgreSQL transactions + FOR UPDATE | Atomic, prevents race conditions |
| Payment | Interface + Dummy implementation | Deferred complexity, testable |
| Frontend | Tailwind CSS + Blade | Server-side, mobile-responsive |
| Migrations | Laravel migrations + seeders | Version control, repeatable setup |

---

## Open Questions Resolved

1. **Q**: Should time slots be pre-generated in database?  
   **A**: No - calculate availability on-demand from bookings table to reduce complexity

2. **Q**: How to handle concurrent booking attempts?  
   **A**: PostgreSQL SELECT FOR UPDATE ensures only first user gets lock

3. **Q**: Separate roles table or enum column?  
   **A**: Enum column sufficient for binary admin/user distinction

4. **Q**: Client-side or server-side rendering?  
   **A**: Server-side (Blade) aligns with simplicity principle, adequate for MVP

5. **Q**: How to test without real payment gateway?  
   **A**: Dummy payment service implementation for MVP phase

---

**Status**: All technical unknowns resolved. Ready for Phase 1 (data model & contracts).
