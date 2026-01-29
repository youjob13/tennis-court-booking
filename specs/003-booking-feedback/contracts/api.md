# API Contracts: Real-Time Booking Validation & Feedback

**Feature**: 003-booking-feedback  
**Created**: 2026-01-28  
**Purpose**: Define new/modified API endpoints

## New Endpoints

### GET /api/courts/{court}/availability/durations

**Purpose**: Get available duration options for a selected start time (AJAX endpoint for dynamic duration dropdown)

**Middleware**: None (public endpoint for real-time validation)

**Path Parameters**:
- `court`: Court ID (integer)

**Query Parameters**:
- `datetime`: Start datetime in ISO format (e.g., "2026-01-28 14:00:00")

**Request Example**:
```http
GET /api/courts/1/availability/durations?datetime=2026-01-28%2014:00:00
```

**Success Response** (200 OK):
```json
{
    "durations": [1, 2, 3],
    "max_duration": 3,
    "reason": "4+ hours conflicts with existing booking at 5 PM"
}
```

**Response Fields**:
- `durations` (array<int>): List of valid duration options in hours
- `max_duration` (int): Maximum available consecutive hours
- `reason` (string|null): Human-readable explanation if max_duration < 8

**Error Responses**:
- 404 Not Found: Court does not exist
- 422 Validation Error: Invalid datetime format

**Implementation**:
```php
// routes/api.php
Route::get('/courts/{court}/availability/durations', [CourtController::class, 'getAvailableDurations']);

// app/Http/Controllers/CourtController.php
public function getAvailableDurations(Court $court, Request $request)
{
    $validated = $request->validate([
        'datetime' => 'required|date|after:now',
    ]);
    
    $durations = $this->availabilityService->getAvailableDurationsForSlot(
        $court->id,
        $validated['datetime']
    );
    
    $maxDuration = !empty($durations) ? max($durations) : 0;
    $reason = null;
    
    if ($maxDuration < 8) {
        // Check if there's a conflicting booking
        $conflictTime = Carbon::parse($validated['datetime'])->addHours($maxDuration + 1);
        $reason = "$maxDuration+ hours conflicts with existing booking at " . $conflictTime->format('g A');
    }
    
    return response()->json([
        'durations' => $durations,
        'max_duration' => $maxDuration,
        'reason' => $reason,
    ]);
}
```

---

## Modified Endpoints

### POST /bookings

**Purpose**: Create booking (lock time slot) and redirect to payment

**Middleware**: auth

**Controller**: BookingController@store

**Request Body** (unchanged):
```json
{
    "court_id": 1,
    "start_datetime": "2026-01-28 14:00:00",
    "duration_hours": 4
}
```

**Validation** (unchanged):
- court_id: required, exists:courts,id
- start_datetime: required, date, after:now
- duration_hours: required, integer, between:1,8

**Business Logic** (enhanced):
1. Check court is active
2. **NEW**: Check if all hours in duration are available (not just start slot)
3. **NEW**: Provide specific error message for failure type (booked vs locked vs conflict)
4. Acquire database lock (SELECT FOR UPDATE)
5. Create booking with status='locked', lock_expires_at=now+15min
6. Calculate total_price (hourly_price × duration)
7. Redirect to payment page

**Success Response** (unchanged): Redirect to `/bookings/{id}/payment`

**Error Responses** (enhanced):

**409 Conflict - Slot Already Booked**:
```json
{
    "message": "This time slot is already booked. Please select a different time.",
    "errors": {
        "start_datetime": ["This time slot is already booked. Please select a different time."]
    }
}
```

**409 Conflict - Slot Locked by Another User**:
```json
{
    "message": "This time slot is temporarily reserved. Please wait or select a different time.",
    "errors": {
        "start_datetime": ["This time slot is temporarily reserved. Please wait or select a different time."]
    }
}
```

**422 Validation Error - Duration Too Long**:
```json
{
    "message": "Selected duration conflicts with existing bookings. Maximum available duration is 3 hour(s).",
    "errors": {
        "duration_hours": ["Selected duration conflicts with existing bookings. Maximum available duration is 3 hour(s)."]
    }
}
```

**422 Validation Error - Outside Operating Hours**:
```json
{
    "message": "Booking must be within operating hours (8 AM - 10 PM).",
    "errors": {
        "start_datetime": ["Booking must be within operating hours (8 AM - 10 PM)."]
    }
}
```

**409 Conflict - Race Condition**:
```json
{
    "message": "This slot was just booked by another user. Please select a different time.",
    "errors": {
        "start_datetime": ["This slot was just booked by another user. Please select a different time."]
    }
}
```

---

### GET /courts/{court}

**Purpose**: Display court details and booking form

**Middleware**: auth

**Controller**: CourtController@show

**Path Parameters**:
- `court`: Court ID (integer)

**Response** (enhanced):
- Court details (unchanged)
- **MODIFIED**: Availability data now includes ALL occupied slots for multi-hour bookings
- **NEW**: Include booking metadata (booking_id, booking_range) for disabled slots

**View Data Structure** (enhanced):
```php
[
    'court' => Court,
    'availability' => [
        '2026-01-28' => [
            'available' => ['08:00', '09:00', '10:00'],
            'booked' => [
                '14:00' => ['booking_id' => 123, 'range' => '2 PM - 6 PM'],
                '15:00' => ['booking_id' => 123, 'range' => '2 PM - 6 PM'],
                '16:00' => ['booking_id' => 123, 'range' => '2 PM - 6 PM'],
                '17:00' => ['booking_id' => 123, 'range' => '2 PM - 6 PM'],
            ],
            'locked' => [
                '18:00' => ['booking_id' => 124, 'expires_at' => '2026-01-28 18:15:00'],
            ],
        ],
        '2026-01-29' => [...],
    ],
]
```

**Implementation Changes**:
```php
// app/Http/Controllers/CourtController.php
public function show(Court $court)
{
    $availability = [];
    
    for ($i = 0; $i < 7; $i++) {
        $date = now()->addDays($i)->toDateString();
        $slots = $this->availabilityService->getAllSlotsWithStatus($court->id, $date);
        
        $availability[$date] = [
            'available' => array_filter($slots, fn($s) => $s['status'] === 'available'),
            'booked' => array_filter($slots, fn($s) => $s['status'] === 'booked'),
            'locked' => array_filter($slots, fn($s) => $s['status'] === 'locked'),
        ];
    }
    
    return view('courts.show', compact('court', 'availability'));
}
```

---

## Service Layer Contracts

### AvailabilityService

**New Methods**:

#### `calculateOccupiedSlots(Booking $booking): array`

**Purpose**: Calculate all hourly slots occupied by a booking

**Input**:
- `$booking` (Booking): Booking instance with start_datetime and duration_hours

**Output**: Array of time strings in HH:MM format

**Example**:
```php
$booking = Booking::find(123); // start_datetime = "2026-01-28 14:00:00", duration_hours = 4
$occupiedSlots = $service->calculateOccupiedSlots($booking);
// Returns: ["14:00", "15:00", "16:00", "17:00"]
```

---

#### `getAvailableDurationsForSlot(int $courtId, string $datetime): array`

**Purpose**: Calculate which duration options (1-8 hours) are valid for a selected start time

**Input**:
- `$courtId` (int): Court ID
- `$datetime` (string): Start datetime in Y-m-d H:i:s format

**Output**: Array of valid duration integers (consecutive only)

**Business Logic**:
- Check if 1-hour booking is valid → Add 1 to result
- Check if 2-hour booking is valid → Add 2 to result
- Continue until first conflict OR reach 8 hours
- Stop at first unavailable duration (must be consecutive)

**Example**:
```php
$durations = $service->getAvailableDurationsForSlot(1, "2026-01-28 14:00:00");
// If 14:00, 15:00, 16:00 are available but 17:00 is booked:
// Returns: [1, 2, 3] (not [1, 2, 3, 4] because 4 hours would include unavailable 17:00)
```

---

#### `getAllSlotsWithStatus(int $courtId, string $date): array`

**Purpose**: Get all time slots for a date with their availability status and metadata

**Input**:
- `$courtId` (int): Court ID
- `$date` (string): Date in Y-m-d format

**Output**: Array of TimeSlot objects (associative arrays)

**Example**:
```php
$slots = $service->getAllSlotsWithStatus(1, "2026-01-28");
// Returns:
[
    ["time" => "08:00", "status" => "available", "booking_id" => null, "booking_range" => null],
    ["time" => "09:00", "status" => "available", "booking_id" => null, "booking_range" => null],
    ["time" => "14:00", "status" => "booked", "booking_id" => 123, "booking_range" => "2 PM - 6 PM"],
    ["time" => "15:00", "status" => "booked", "booking_id" => 123, "booking_range" => "2 PM - 6 PM"],
    ["time" => "18:00", "status" => "locked", "booking_id" => 124, "booking_range" => null],
]
```

---

**Modified Methods**:

#### `getAvailabilityForDate(int $courtId, string $date): array`

**Current Behavior**: Returns array with 'available', 'booked', 'locked' arrays containing START times only

**Enhanced Behavior**: Include ALL occupied slots for multi-hour bookings

**Before**:
```php
// Booking: start_datetime = "14:00", duration_hours = 4
[
    'available' => ['08:00', '09:00', '10:00'],
    'booked' => ['14:00'], // Only start time
    'locked' => [],
]
```

**After**:
```php
// Booking: start_datetime = "14:00", duration_hours = 4
[
    'available' => ['08:00', '09:00', '10:00'],
    'booked' => ['14:00', '15:00', '16:00', '17:00'], // All occupied slots
    'locked' => [],
]
```

**Implementation Change**:
```php
public function getAvailabilityForDate(int $courtId, string $date): array
{
    // ... existing code to get allSlots and bookings ...
    
    $booked = [];
    $locked = [];
    
    foreach ($bookings as $booking) {
        // OLD: $slotTime = $booking->start_datetime->format('H:i');
        // NEW: Calculate all occupied slots
        $occupiedSlots = $this->calculateOccupiedSlots($booking);
        
        if ($booking->status === 'confirmed') {
            $booked = array_merge($booked, $occupiedSlots);
        } elseif ($booking->status === 'locked') {
            $locked = array_merge($locked, $occupiedSlots);
        }
    }
    
    // ... rest of method unchanged ...
}
```

---

## Frontend JavaScript Contracts

### Alpine.js Data Structure (courts/show.blade.php)

**Timezone Conversion Utility**:
```javascript
// Add before Alpine.js component
function formatLocalTime(utcDatetimeString) {
    const date = new Date(utcDatetimeString);
    const formatter = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        timeZoneName: 'short'
    });
    return formatter.format(date); // Returns "2:00 PM EST"
}

function isPastSlot(utcDatetimeString) {
    const slotTime = new Date(utcDatetimeString);
    const now = new Date();
    return slotTime < now;
}
```

**Current**:
```javascript
{
    loading: false
}
```

**Enhanced**:
```javascript
{
    loading: false,
    selectedDate: '',
    selectedTime: '',
    selectedDuration: '',
    availableDurations: [],
    isLoadingDurations: false,
    
    async fetchAvailableDurations() {
        if (!this.selectedDate || !this.selectedTime) return;
        
        this.isLoadingDurations = true;
        const datetime = this.selectedDate + ' ' + this.selectedTime + ':00';
        
        try {
            const response = await fetch(`/api/courts/${courtId}/availability/durations?datetime=${encodeURIComponent(datetime)}`);
            const data = await response.json();
            this.availableDurations = data.durations;
        } catch (error) {
            console.error('Failed to fetch available durations:', error);
            this.availableDurations = [];
        } finally {
            this.isLoadingDurations = false;
        }
    },
    
    isValid() {
        return this.selectedDate && 
               this.selectedTime && 
               this.selectedDuration && 
               this.availableDurations.includes(parseInt(this.selectedDuration));
    }
}
```

**Reactive Behavior**:
- When `selectedTime` changes → Call `fetchAvailableDurations()` → Update `availableDurations` array → Update duration dropdown options
- When `selectedDuration` changes → Update total price display
- Submit button disabled when `!isValid()`

---

## Summary

**New Endpoints**:
- GET `/api/courts/{court}/availability/durations` - Dynamic duration validation

**Modified Endpoints**:
- POST `/bookings` - Enhanced error messages (5 distinct scenarios)
- GET `/courts/{court}` - Enhanced availability data (multi-hour slot occupancy)

**New Service Methods**:
- `AvailabilityService::calculateOccupiedSlots()`
- `AvailabilityService::getAvailableDurationsForSlot()`
- `AvailabilityService::getAllSlotsWithStatus()`

**Modified Service Methods**:
- `AvailabilityService::getAvailabilityForDate()` - Include all occupied slots

**Frontend Enhancements**:
- Alpine.js reactive data for duration validation
- AJAX call to duration validation endpoint
- Dynamic duration dropdown updates
- Submit button validation
