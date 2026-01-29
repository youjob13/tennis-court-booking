# Data Model: Real-Time Booking Validation & Feedback

**Feature**: 003-booking-feedback  
**Created**: 2026-01-28  
**Purpose**: Define data structures and relationships

## Entities

### Booking (Existing - No Changes)

**Purpose**: Represents a tennis court reservation with lock/confirmed/cancelled status

**Attributes**:
- `id`: Primary key (integer, auto-increment)
- `court_id`: Foreign key to courts table (integer, required)
- `user_id`: Foreign key to users table (integer, required)
- `start_datetime`: Booking start timestamp (timestampTz, required)
- `duration_hours`: Booking duration in hours (integer, 1-8, required)
- `total_price`: Total booking cost (decimal(10,2), required)
- `status`: Booking status enum ('locked', 'confirmed', 'cancelled')
- `payment_reference`: Payment gateway reference (string, nullable)
- `lock_expires_at`: Lock expiration timestamp (timestamp, nullable)
- `unlocked_after`: Delay before slot unlocks after failed payment (timestamp, nullable)
- `created_at`: Record creation timestamp (timestamp, auto)
- `updated_at`: Record update timestamp (timestamp, auto)

**Relationships**:
- Belongs to Court (many-to-one)
- Belongs to User (many-to-one)

**Indexes**:
- Unique: (court_id, start_datetime) - Prevents duplicate bookings
- Index: status - For filtering by booking status
- Index: lock_expires_at - For cleanup scheduled tasks

**Validation Rules**:
- duration_hours: Between 1 and 8 hours
- start_datetime: Must be after current time (server validates in UTC)
- start_datetime: Must be within court operating hours
- Slot availability: No overlapping bookings for same court
- Timezone handling: All datetimes stored in UTC, converted to user's local timezone on display
- Past slot filtering: Slots before current time (in user's timezone) disabled with "Time has passed" message

**Business Logic**:
- Multi-hour bookings occupy consecutive hourly slots
- Formula: Occupied slots = [start_datetime + 0h, start_datetime + 1h, ..., start_datetime + (duration_hours - 1)h]
- Example: 2 PM booking with 4 hours occupies 2 PM, 3 PM, 4 PM, 5 PM slots

---

### Court (Existing - No Changes)

**Purpose**: Represents a tennis court with availability and pricing

**Attributes**:
- `id`: Primary key
- `name`: Court name (string, required)
- `description`: Court description (text, nullable)
- `hourly_price`: Hourly rental price (decimal(10,2), required)
- `status`: Court status enum ('active', 'disabled')
- `operating_hours`: Operating hours JSON ({"start": "08:00", "end": "22:00"})
- `photo_url`: Court photo URL (string, nullable)

**Relationships**:
- Has many Bookings (one-to-many)

---

### TimeSlot (Calculated - Not Stored)

**Purpose**: Represents a single hourly time slot with availability status (derived data, not persisted)

**Calculated Attributes**:
- `time`: Time in HH:MM format (e.g., "14:00" for 2 PM) - stored in UTC, converted to user's local timezone for display
- `date`: Date in Y-m-d format (e.g., "2026-01-28")
- `datetime`: Full ISO timestamp (e.g., "2026-01-28 14:00:00") - UTC in database
- `status`: Availability status enum ('available', 'booked', 'locked', 'past')
- `booking_id`: Associated booking ID if status is 'booked' or 'locked' (nullable)
- `booking_range`: Human-readable time range for booked/locked slots in user's timezone (e.g., "2:00 PM - 6:00 PM EST")
- `is_past`: Boolean indicating if slot start time is before current time (calculated in user's browser timezone)

**Calculation Logic**:
1. Generate all hourly slots within court operating hours (e.g., 08:00, 09:00, ..., 21:00)
2. For each slot, check if any booking occupies it:
   - If booking.status = 'confirmed' AND slot is within [start_datetime, start_datetime + duration_hours) → status = 'booked'
   - If booking.status = 'locked' AND slot is within [start_datetime, start_datetime + duration_hours) → status = 'locked'
   - Otherwise → status = 'available'
3. Store booking_id and calculate booking_range for display purposes

**Example**:
```
Court operating hours: 08:00 - 22:00
Existing booking: start_datetime = "2026-01-28 14:00:00", duration_hours = 4, status = 'confirmed'

Calculated TimeSlots:
[
    {"time": "08:00", "status": "available", "booking_id": null},
    {"time": "09:00", "status": "available", "booking_id": null},
    ...
    {"time": "14:00", "status": "booked", "booking_id": 123, "booking_range": "2 PM - 6 PM"},
    {"time": "15:00", "status": "booked", "booking_id": 123, "booking_range": "2 PM - 6 PM"},
    {"time": "16:00", "status": "booked", "booking_id": 123, "booking_range": "2 PM - 6 PM"},
    {"time": "17:00", "status": "booked", "booking_id": 123, "booking_range": "2 PM - 6 PM"},
    {"time": "18:00", "status": "available", "booking_id": null},
    ...
]
```

---

## Service Layer Methods

### AvailabilityService (Enhanced)

**New Methods**:

1. `calculateOccupiedSlots(Booking $booking): array`
   - **Purpose**: Calculate all hourly slots occupied by a booking
   - **Input**: Booking instance
   - **Output**: Array of time strings in HH:MM format (e.g., ["14:00", "15:00", "16:00", "17:00"])
   - **Logic**: Loop from 0 to (duration_hours - 1), add hours to start_datetime

2. `getAvailableDurationsForSlot(int $courtId, string $datetime): array`
   - **Purpose**: Calculate which duration options (1-8 hours) are valid for a selected start time
   - **Input**: Court ID, start datetime string
   - **Output**: Array of valid duration integers (e.g., [1, 2, 3] means 1-3 hours available)
   - **Logic**: Check consecutive slot availability using existing `isSlotAvailable()` method; stop at first conflict

3. `getAllSlotsWithStatus(int $courtId, string $date): array`
   - **Purpose**: Get all time slots for a date with their availability status
   - **Input**: Court ID, date string (Y-m-d)
   - **Output**: Array of TimeSlot objects (calculated, not persisted)
   - **Logic**: Generate all slots from operating hours, mark each as available/booked/locked based on bookings

**Modified Methods**:

1. `getAvailabilityForDate(int $courtId, string $date): array`
   - **Current**: Returns `['available' => [...], 'booked' => [...], 'locked' => [...]]` with START times only
   - **Enhancement**: Include ALL occupied slots for multi-hour bookings
   - **Example**: 2 PM booking with 4 hours → booked array includes ["14:00", "15:00", "16:00", "17:00"], not just ["14:00"]

---

## Data Flow

### Booking Form Load (courts/show.blade.php)

1. **Controller** (CourtController@show):
   - Fetch court details
   - Call `AvailabilityService::getAvailabilityForDate()` for next 7 days
   - Pass availability data to view

2. **View** (courts/show.blade.php):
   - Display date dropdown with all available dates
   - Display duration dropdown (1-8 hours initially, all enabled)
   - Display time slot grid (initially empty, "Please select a date first")

3. **User Action** - Select Date:
   - Alpine.js updates `selectedDate`
   - JavaScript filters availability data for selected date
   - **JavaScript converts UTC times to user's local timezone using Intl.DateTimeFormat**
   - **JavaScript filters out past slots (where slot time < current time in user's timezone)**
   - Display ALL slots with visual states:
     - Available slots (future): Clickable, green Badge (success variant), time in user's timezone with timezone abbreviation (e.g., "2:00 PM EST")
     - Past available slots: Disabled, gray Badge (secondary variant), tooltip "Time has passed"
     - Booked slots: Disabled, gray Badge (secondary variant), tooltip "Booked: 2:00 PM - 6:00 PM EST"
     - Locked slots: Disabled, yellow Badge (warning variant), tooltip "Locked: Payment pending"

4. **User Action** - Select Time Slot:
   - Alpine.js updates `selectedTime`
   - AJAX call to `/api/availability/durations?court_id={id}&datetime={datetime}` (NEW ENDPOINT)
   - Server calls `AvailabilityService::getAvailableDurationsForSlot()`
   - Update duration dropdown: Disable invalid options, enable only available durations
   - Update submit button state based on validation

5. **User Action** - Select Duration:
   - Alpine.js updates `selectedDuration`
   - Recalculate total price
   - Validate selection (date + time + duration all selected and valid)
   - Enable/disable submit button

6. **User Action** - Submit Form:
   - POST to `/bookings` with court_id, start_datetime, duration_hours
   - Server-side validation (duplicate check, race condition handling)
   - If valid: Create locked booking, redirect to payment page
   - If invalid: Return with specific error message

---

## Validation Rules

### Client-Side (Alpine.js)

- Date must be selected
- Time slot must be selected (and must be available)
- Duration must be selected (and must be in available durations list)
- Submit button disabled until all validations pass

### Server-Side (BookingController)

- Court ID exists and court is active
- Start datetime is after current time
- Start datetime is within court operating hours
- Duration is between 1-8 hours
- Slot availability check (no overlapping bookings)
- Database lock acquisition (SELECT FOR UPDATE to prevent race conditions)

**Error Messages** (FR-009):
- "This time slot is already booked. Please select a different time."
- "This time slot is temporarily reserved. Please wait or select a different time."
- "Selected duration conflicts with existing bookings. Maximum available duration is X hour(s)."
- "Booking must be within operating hours (8 AM - 10 PM)."
- "This slot was just booked by another user. Please select a different time." (race condition)

---

## Database Changes

**None required** - Feature uses existing schema with enhanced business logic in service layer.
