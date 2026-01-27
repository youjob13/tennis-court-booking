# Data Model: Tennis Court Booking System

**Purpose**: Define database schema and entity relationships  
**Date**: 2026-01-27  
**Database**: PostgreSQL 15+

## Entity Relationship Overview

```
User (1) ----< (many) Booking (many) >---- (1) Court
  ^                      |
  |                      |
  +-- role: admin        +-- status: locked|confirmed
```

## Entities

### 1. User

**Purpose**: Represents registered users (both regular users and administrators)

**Attributes**:
- `id`: BIGINT PRIMARY KEY AUTO_INCREMENT
- `email`: VARCHAR(255) UNIQUE NOT NULL - User's email address (login identifier)
- `password`: VARCHAR(255) NOT NULL - Hashed password (bcrypt)
- `name`: VARCHAR(255) NOT NULL - Full name
- `phone`: VARCHAR(50) - Phone number
- `role`: ENUM('user', 'admin') DEFAULT 'user' - User role for access control
- `email_verified_at`: TIMESTAMP NULL - Email verification timestamp
- `remember_token`: VARCHAR(100) NULL - Laravel remember me token
- `created_at`: TIMESTAMP - Account creation timestamp
- `updated_at`: TIMESTAMP - Last update timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE INDEX on `email`
- INDEX on `role` for admin queries

**Validation Rules**:
- Email must be valid format and unique
- Password minimum 8 characters
- Phone optional but must match format if provided
- Role defaults to 'user', only settable directly in database

**Relationships**:
- Has many Bookings (as booker)

---

### 2. Court

**Purpose**: Represents a tennis court available for booking

**Attributes**:
- `id`: BIGINT PRIMARY KEY AUTO_INCREMENT
- `name`: VARCHAR(255) NOT NULL - Court name/identifier (e.g., "Court 1", "Center Court")
- `description`: TEXT - Court description and amenities
- `photo_url`: VARCHAR(500) - URL to court photo
- `hourly_price`: DECIMAL(8,2) NOT NULL - Price per hour in currency units
- `status`: ENUM('active', 'disabled') DEFAULT 'active' - Court availability status
- `operating_hours`: JSON - Court operating schedule (e.g., {"start": "08:00", "end": "22:00"})
- `created_at`: TIMESTAMP - Creation timestamp
- `updated_at`: TIMESTAMP - Last update timestamp

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `status` for availability queries

**Validation Rules**:
- Name required, max 255 characters
- Hourly price must be positive decimal
- Status defaults to 'active'
- Operating hours JSON must contain valid time strings

**Relationships**:
- Has many Bookings

**Business Rules**:
- Cannot be deleted if future bookings exist
- When disabled, existing bookings remain valid, new bookings prevented
- Operating hours apply to all days (future: per-day schedules)

---

### 3. Booking

**Purpose**: Represents a court reservation (both locked and confirmed bookings)

**Attributes**:
- `id`: BIGINT PRIMARY KEY AUTO_INCREMENT
- `court_id`: BIGINT NOT NULL FOREIGN KEY -> courts(id)
- `user_id`: BIGINT NOT NULL FOREIGN KEY -> users(id)
- `start_datetime`: TIMESTAMP WITH TIME ZONE NOT NULL - Booking start time
- `duration_hours`: INTEGER NOT NULL - Booking duration (1-8 hours)
- `total_price`: DECIMAL(10,2) NOT NULL - Total booking cost (hourly_price * duration)
- `status`: ENUM('locked', 'confirmed', 'cancelled') NOT NULL - Booking status
- `payment_reference`: VARCHAR(255) NULL - External payment gateway reference
- `lock_expires_at`: TIMESTAMP NULL - Lock expiration time (NULL if confirmed)
- `unlocked_after`: TIMESTAMP NULL - Timestamp when lock can be released after payment failure
- `created_at`: TIMESTAMP - Booking creation timestamp
- `updated_at`: TIMESTAMP - Last update timestamp

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE INDEX on `(court_id, start_datetime)` - Prevents double booking at same time
- INDEX on `status` for lock/confirmation queries
- INDEX on `lock_expires_at` for expired lock cleanup
- INDEX on `user_id` for user booking history

**Validation Rules**:
- Duration must be between 1 and 8 hours
- Start datetime must be in the future
- Total price must match court hourly_price * duration_hours
- Status transitions: locked -> confirmed OR locked -> cancelled
- Lock expires at set when status is 'locked', NULL when confirmed

**Relationships**:
- Belongs to User
- Belongs to Court

**Business Rules**:
- Unique constraint on (court_id, start_datetime) prevents overlapping bookings
- Lock status bookings expire after 10 minutes if not confirmed
- Confirmed bookings cannot be cancelled by admin
- Payment failure releases lock after 30 seconds (via unlocked_after timestamp)

---

## Time Slot Calculation (No Separate Table)

**Approach**: Calculate available time slots dynamically from bookings table

**Query Logic**:
```sql
-- Example: Get available slots for a specific court and date
SELECT generate_series(
    '[DATE] 08:00'::timestamp,
    '[DATE] 22:00'::timestamp,
    '1 hour'::interval
) AS slot_start
WHERE slot_start NOT IN (
    SELECT start_datetime
    FROM bookings
    WHERE court_id = [COURT_ID]
      AND DATE(start_datetime) = '[DATE]'
      AND status IN ('locked', 'confirmed')
)
```

**Rationale**:
- Avoids pre-generating and maintaining time slot records
- Reduces database complexity
- Easier to handle variable operating hours per court
- Calculation performed on-demand in application layer

---

## Migrations Overview

### Migration 1: Create Users Table
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('password');
    $table->string('name');
    $table->string('phone', 50)->nullable();
    $table->enum('role', ['user', 'admin'])->default('user');
    $table->timestamp('email_verified_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
    
    $table->index('role');
});
```

### Migration 2: Create Courts Table
```php
Schema::create('courts', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('photo_url', 500)->nullable();
    $table->decimal('hourly_price', 8, 2);
    $table->enum('status', ['active', 'disabled'])->default('active');
    $table->json('operating_hours')->nullable();
    $table->timestamps();
    
    $table->index('status');
});
```

### Migration 3: Create Bookings Table
```php
Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('court_id')->constrained()->onDelete('restrict');
    $table->foreignId('user_id')->constrained()->onDelete('restrict');
    $table->timestampTz('start_datetime');
    $table->integer('duration_hours');
    $table->decimal('total_price', 10, 2);
    $table->enum('status', ['locked', 'confirmed', 'cancelled']);
    $table->string('payment_reference')->nullable();
    $table->timestamp('lock_expires_at')->nullable();
    $table->timestamp('unlocked_after')->nullable();
    $table->timestamps();
    
    $table->unique(['court_id', 'start_datetime']);
    $table->index('status');
    $table->index('lock_expires_at');
    $table->index('user_id');
});
```

---

## Seeders

### CourtSeeder
```php
Court::create([
    'name' => 'Center Court',
    'description' => 'Premium court with lighting and seating',
    'photo_url' => 'https://via.placeholder.com/400x300?text=Center+Court',
    'hourly_price' => 50.00,
    'status' => 'active',
    'operating_hours' => json_encode(['start' => '08:00', 'end' => '22:00'])
]);

// Create 5-10 courts with varying prices and descriptions
```

### UserSeeder (Development Only)
```php
User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'phone' => '555-0100',
    'role' => 'admin',
    'email_verified_at' => now()
]);

User::create([
    'name' => 'Test User',
    'email' => 'user@example.com',
    'password' => Hash::make('password'),
    'phone' => '555-0101',
    'role' => 'user',
    'email_verified_at' => now()
]);
```

---

## Data Integrity Constraints

### Foreign Key Constraints
- `bookings.court_id` REFERENCES `courts.id` ON DELETE RESTRICT
- `bookings.user_id` REFERENCES `users.id` ON DELETE RESTRICT

**Rationale**: RESTRICT prevents deletion of courts/users with booking history. Alternative: SET NULL to preserve booking records for analytics.

### Unique Constraints
- `users.email` - Prevents duplicate accounts
- `(bookings.court_id, bookings.start_datetime)` - Prevents double booking

### Check Constraints (Application Level)
- Duration between 1-8 hours
- Hourly price > 0
- Start datetime > NOW() when creating booking
- Total price matches hourly rate × duration

---

## Performance Considerations

### Indexing Strategy
- Unique index on bookings `(court_id, start_datetime)` serves dual purpose: constraint + query optimization
- Status indexes for filtering locked/confirmed bookings
- Lock expiration index for scheduled cleanup job

### Expected Query Patterns
1. **List courts with availability** - Frequent, requires join to bookings
2. **Check specific slot availability** - Very frequent, needs fast lookup
3. **User's booking history** - Less frequent, indexed by user_id
4. **Admin view locked bookings** - Admin only, filtered by status

### Optimization Notes
- Time slot calculation can be cached for current day
- Consider materialized view for availability if performance issues arise (post-MVP)
- Booking cleanup job runs every minute - minimal impact with indexed lock_expires_at

---

## Schema Evolution Considerations

### Future Extensions (Out of MVP Scope)
- `booking_cancellations` table for refund tracking
- `payments` table for detailed payment history
- `court_availability_exceptions` for maintenance schedules
- `user_profiles` for extended user information
- `notifications` table for booking reminders

### Migration Strategy
- All migrations reversible via `down()` methods
- Seeders separate from migrations for production safety
- Schema changes deployed via `php artisan migrate`
- Data model changes require new migration file

---

**Status**: Data model complete and validated against specification requirements.
