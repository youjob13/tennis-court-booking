# Quick Start: Real-Time Booking Validation & Feedback

**Feature**: 003-booking-feedback  
**Created**: 2026-01-28  
**Purpose**: Get started with implementing booking feedback improvements

## Overview

This feature enhances the tennis court booking system to provide immediate user feedback:
1. **Pre-booking validation**: Users see slot availability BEFORE form submission
2. **Multi-hour slot visualization**: All consecutive slots occupied by a booking are displayed
3. **Visible disabled states**: Unavailable slots shown with clear styling instead of hidden

## Prerequisites

- Feature 001 (tennis-court-booking) fully implemented
- Feature 002 (design-polish) fully implemented (Badge and Card components available)
- Laravel 11.x with Blade templating
- Alpine.js 3.x for reactive forms
- Tailwind CSS 3.x for styling
- Database seeded with courts and sample bookings

## Key Concepts

### Multi-Hour Slot Occupancy

A booking with `start_datetime = "2026-01-28 14:00:00"` and `duration_hours = 4` occupies FOUR hourly slots:
- 14:00 (2 PM)
- 15:00 (3 PM)
- 16:00 (4 PM)
- 17:00 (5 PM)

**Current behavior**: Only start time (14:00) is marked as unavailable  
**New behavior**: All four slots (14:00, 15:00, 16:00, 17:00) are marked as unavailable

### Dynamic Duration Validation

When user selects a start time (e.g., 2 PM), the system calculates which duration options are valid:
- Check 1-hour availability: 2 PM available? → Show "1 hour" option
- Check 2-hour availability: 2 PM AND 3 PM available? → Show "2 hours" option
- Check 3-hour availability: 2 PM AND 3 PM AND 4 PM available? → Show "3 hours" option
- Stop at first conflict: If 5 PM is booked, don't show "4 hours" or longer options

**Result**: Duration dropdown only shows valid options (prevents invalid selections)

### Slot Status Display

All slots within operating hours are displayed with one of three states:
- **Available** (green Badge): Clickable, user can select
- **Booked** (gray Badge): Disabled, tooltip shows "Booked: 2 PM - 6 PM"
- **Locked** (yellow Badge): Disabled, tooltip shows "Locked: Payment pending"

## Implementation Phases

### Phase 0: Service Layer - Multi-Hour Slot Calculation

**File**: `app/Services/AvailabilityService.php`

**Add method**:
```php
public function calculateOccupiedSlots(Booking $booking): array
{
    $slots = [];
    $startTime = Carbon::parse($booking->start_datetime);
    
    for ($i = 0; $i < $booking->duration_hours; $i++) {
        $slots[] = $startTime->copy()->addHours($i)->format('H:i');
    }
    
    return $slots;
}
```

**Modify method** `getAvailabilityForDate()`:
```php
foreach ($bookings as $booking) {
    // OLD: $slotTime = $booking->start_datetime->format('H:i');
    // NEW:
    $occupiedSlots = $this->calculateOccupiedSlots($booking);
    
    if ($booking->status === 'confirmed') {
        $booked = array_merge($booked, $occupiedSlots);
    } elseif ($booking->status === 'locked') {
        $locked = array_merge($locked, $occupiedSlots);
    }
}
```

**Test**: Create 4-hour booking, check that all 4 slots show as booked on court listing page

---

### Phase 1: Service Layer - Dynamic Duration Validation

**File**: `app/Services/AvailabilityService.php`

**Add method**:
```php
public function getAvailableDurationsForSlot(int $courtId, string $datetime): array
{
    $maxDuration = 8;
    $availableDurations = [];
    
    for ($duration = 1; $duration <= $maxDuration; $duration++) {
        if ($this->isSlotAvailable($courtId, $datetime, $duration)) {
            $availableDurations[] = $duration;
        } else {
            break; // Stop at first unavailable duration (consecutive check)
        }
    }
    
    return $availableDurations;
}
```

**Test**: Call method with start time 2 PM when 5 PM is booked, verify returns [1, 2, 3] (not [1, 2, 3, 4])

---

### Phase 2: API Endpoint - Duration Validation

**File**: `routes/api.php`

**Add route**:
```php
Route::get('/courts/{court}/availability/durations', [CourtController::class, 'getAvailableDurations']);
```

**File**: `app/Http/Controllers/CourtController.php`

**Add method**:
```php
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
        $conflictTime = Carbon::parse($validated['datetime'])->addHours($maxDuration + 1);
        $reason = ($maxDuration + 1) . "+ hours conflicts with existing booking at " . $conflictTime->format('g A');
    }
    
    return response()->json([
        'durations' => $durations,
        'max_duration' => $maxDuration,
        'reason' => $reason,
    ]);
}
```

**Test**: `GET /api/courts/1/availability/durations?datetime=2026-01-28%2014:00:00` → Returns JSON with durations array

---

### Phase 3: Frontend - Alpine.js Dynamic Validation

**File**: `resources/views/courts/show.blade.php`

**Replace** `x-data="{ loading: false }"` with:
```html
<div x-data="{
    loading: false,
    selectedDate: '',
    selectedTime: '',
    selectedDuration: '',
    availableDurations: [1, 2, 3, 4, 5, 6, 7, 8],
    isLoadingDurations: false,
    
    async fetchAvailableDurations() {
        if (!this.selectedDate || !this.selectedTime) {
            this.availableDurations = [1, 2, 3, 4, 5, 6, 7, 8];
            return;
        }
        
        this.isLoadingDurations = true;
        const datetime = this.selectedDate + ' ' + this.selectedTime + ':00';
        
        try {
            const response = await fetch(`/api/courts/{{ $court->id }}/availability/durations?datetime=${encodeURIComponent(datetime)}`);
            const data = await response.json();
            this.availableDurations = data.durations;
            
            // Clear selected duration if no longer valid
            if (!this.availableDurations.includes(parseInt(this.selectedDuration))) {
                this.selectedDuration = '';
            }
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
}" @time-slot-selected.window="fetchAvailableDurations()">
```

**Update duration dropdown**:
```html
<select name="duration_hours" id="duration_hours" required 
        x-model="selectedDuration"
        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <option value="">Select duration</option>
    <template x-for="duration in availableDurations" :key="duration">
        <option :value="duration" x-text="duration + ' ' + (duration === 1 ? 'hour' : 'hours')"></option>
    </template>
</select>
```

**Update submit button**:
```html
<x-button variant="primary" type="submit" class="w-full" 
          x-bind:disabled="!isValid() || loading" 
          x-bind:loading="loading">
    Proceed to Payment
</x-button>
```

**Update time slot click handler** (in JavaScript section):
```javascript
button.addEventListener('click', function() {
    document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('border-blue-600', 'bg-blue-100'));
    this.classList.add('border-blue-600', 'bg-blue-100');
    selectedSlot = this.dataset.slot;
    
    // Extract time from slot (format: "2026-01-28 14:00:00")
    const timePart = selectedSlot.split(' ')[1].substring(0, 5); // "14:00"
    
    // Update Alpine.js data and trigger duration fetch
    const alpineComponent = document.querySelector('[x-data]').__x.$data;
    alpineComponent.selectedTime = timePart;
    alpineComponent.fetchAvailableDurations();
    
    updateTotalPrice();
});
```

**Test**: Select date, click time slot, observe duration dropdown updates with only valid options

---

### Phase 4: Frontend - Display All Slots with Disabled States

**File**: `resources/views/courts/show.blade.php`

**Add timezone utility functions** (at the top of JavaScript section):
```javascript
// Timezone conversion utilities
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

**Update** `updateTimeSlots()` JavaScript function:
```javascript
function updateTimeSlots() {
    const selectedDate = dateSelect.value;
    
    if (!selectedDate) {
        timeSlotsContainer.innerHTML = '<p class="text-gray-500">Please select a date first</p>';
        return;
    }

    const slots = availability[selectedDate];
    if (!slots) {
        timeSlotsContainer.innerHTML = '<p class="text-gray-500">No availability data</p>';
        return;
    }
    
    // Generate ALL slots (available + booked + locked)
    const allSlots = [
        ...slots.available.map(time => ({ time, status: 'available' })),
        ...Object.entries(slots.booked || {}).map(([time, meta]) => ({ 
            time, 
            status: 'booked', 
            tooltip: `Booked: ${meta.range || ''}` 
        })),
        ...Object.entries(slots.locked || {}).map(([time, meta]) => ({ 
            time, 
            status: 'locked', 
            tooltip: 'Locked: Payment pending' 
        }))
    ].sort((a, b) => a.time.localeCompare(b.time));
    
    const slotsHtml = allSlots.map(slot => {
        const time = slot.time.split(':');
        const hour = parseInt(time[0]);
        const displayTime = (hour < 12 ? hour : (hour === 12 ? 12 : hour - 12)) + (hour < 12 ? ' AM' : ' PM');
        
        const isAvailable = slot.status === 'available';
        const badgeVariant = slot.status === 'available' ? 'success' : (slot.status === 'booked' ? 'secondary' : 'warning');
        const disabledClass = !isAvailable ? 'opacity-50 cursor-not-allowed' : '';
        const disabledAttr = !isAvailable ? 'disabled' : '';
        const tooltip = slot.tooltip || '';
        
        return `
            <button type="button" 
                    class="time-slot p-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors ${disabledClass}"
                    data-slot="${selectedDate} ${slot.time}:00"
                    title="${tooltip}"
                    ${disabledAttr}>
                <span class="px-2 py-1 text-xs font-semibold rounded-full inline-block
                    ${slot.status === 'available' ? 'bg-green-100 text-green-800' : ''}
                    ${slot.status === 'booked' ? 'bg-gray-200 text-gray-600' : ''}
                    ${slot.status === 'locked' ? 'bg-yellow-100 text-yellow-800' : ''}">
                    ${displayTime}
                </span>
            </button>
        `;
    }).join('');

    timeSlotsContainer.innerHTML = `
        <div class="grid grid-cols-4 sm:grid-cols-6 gap-3 mb-4">
            ${slotsHtml}
        </div>
        <div class="flex gap-4 text-xs text-gray-600">
            <div class="flex items-center gap-1">
                <span class="w-3 h-3 bg-green-100 border border-green-300 rounded"></span>
                <span>Available</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="w-3 h-3 bg-gray-200 border border-gray-300 rounded"></span>
                <span>Booked</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="w-3 h-3 bg-yellow-100 border border-yellow-300 rounded"></span>
                <span>Locked</span>
            </div>
        </div>
    `;

    // Add click handlers only to available slots
    document.querySelectorAll('.time-slot:not([disabled])').forEach(button => {
        button.addEventListener('click', function() {
            // ... existing click handler code ...
        });
    });
}
```

**Test**: Select date, observe all slots displayed with green (available), gray (booked), yellow (locked) states

---

### Phase 5: Controller - Enhanced Availability Data

**File**: `app/Http/Controllers/CourtController.php`

**Modify** `show()` method to include booking metadata:
```php
public function show(Court $court)
{
    $availability = [];
    
    for ($i = 0; $i < 7; $i++) {
        $date = now()->addDays($i)->toDateString();
        $dateAvailability = $this->availabilityService->getAvailabilityForDate($court->id, $date);
        
        // Enhance booked/locked slots with metadata
        $bookedWithMeta = [];
        $lockedWithMeta = [];
        
        $bookings = Booking::where('court_id', $court->id)
            ->whereDate('start_datetime', $date)
            ->whereIn('status', ['confirmed', 'locked'])
            ->get();
        
        foreach ($bookings as $booking) {
            $occupiedSlots = $this->availabilityService->calculateOccupiedSlots($booking);
            $range = $booking->start_datetime->format('g A') . ' - ' . 
                     $booking->start_datetime->copy()->addHours($booking->duration_hours)->format('g A');
            
            foreach ($occupiedSlots as $slot) {
                if ($booking->status === 'confirmed') {
                    $bookedWithMeta[$slot] = [
                        'booking_id' => $booking->id,
                        'range' => $range,
                    ];
                } else {
                    $lockedWithMeta[$slot] = [
                        'booking_id' => $booking->id,
                        'expires_at' => $booking->lock_expires_at?->format('g:i A'),
                    ];
                }
            }
        }
        
        $availability[$date] = [
            'available' => $dateAvailability['available'],
            'booked' => $bookedWithMeta,
            'locked' => $lockedWithMeta,
        ];
    }
    
    return view('courts.show', compact('court', 'availability'));
}
```

**Test**: Inspect availability data passed to view, verify booked slots include range metadata

---

### Phase 6: Enhanced Server-Side Validation

**File**: `app/Http/Controllers/BookingController.php`

**Modify** `store()` method to provide specific error messages:
```php
// Check if slot is available
if (!$this->availabilityService->isSlotAvailable(
    $validated['court_id'],
    $validated['start_datetime'],
    $validated['duration_hours']
)) {
    // Check specific reason for unavailability
    $conflictingBooking = Booking::where('court_id', $validated['court_id'])
        ->where(function ($query) use ($validated) {
            $startTime = Carbon::parse($validated['start_datetime']);
            $endTime = $startTime->copy()->addHours($validated['duration_hours']);
            
            $query->whereBetween('start_datetime', [$startTime, $endTime])
                ->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('start_datetime', '<=', $startTime)
                      ->whereRaw('start_datetime + (duration_hours || \' hours\')::interval > ?', [$startTime]);
                });
        })
        ->whereIn('status', ['locked', 'confirmed'])
        ->first();
    
    if ($conflictingBooking && $conflictingBooking->status === 'confirmed') {
        return back()->withErrors([
            'start_datetime' => 'This time slot is already booked. Please select a different time.'
        ])->withInput();
    } elseif ($conflictingBooking && $conflictingBooking->status === 'locked') {
        return back()->withErrors([
            'start_datetime' => 'This time slot is temporarily reserved. Please wait or select a different time.'
        ])->withInput();
    }
    
    // Check if duration is too long
    $maxAvailableDurations = $this->availabilityService->getAvailableDurationsForSlot(
        $validated['court_id'],
        $validated['start_datetime']
    );
    
    if (!empty($maxAvailableDurations)) {
        $maxDuration = max($maxAvailableDurations);
        return back()->withErrors([
            'duration_hours' => "Selected duration conflicts with existing bookings. Maximum available duration is $maxDuration hour(s)."
        ])->withInput();
    }
    
    return back()->withErrors([
        'start_datetime' => 'Time slot is no longer available.'
    ])->withInput();
}
```

**Test**: Attempt to book unavailable slot, verify specific error message displayed

---

## Testing Checklist

### Multi-Hour Slot Display
- [ ] Create 4-hour booking for 2 PM
- [ ] View court listing page → Verify slots 2 PM, 3 PM, 4 PM, 5 PM all show as booked
- [ ] Hover over each slot → Verify tooltip shows "Booked: 2 PM - 6 PM"

### Dynamic Duration Validation
- [ ] Open booking form for court with 2 PM booked
- [ ] Select date and 1 PM start time → Verify duration dropdown shows 1 hour only (2 PM is next slot, booked)
- [ ] Select 12 PM start time → Verify duration dropdown shows 1-2 hours only (3+ hours would overlap 2 PM)

### Disabled Slot Display
- [ ] Open booking form
- [ ] Select date → Verify ALL slots displayed (available + booked + locked)
- [ ] Verify available slots: Green badge, clickable
- [ ] Verify booked slots: Gray badge, disabled, tooltip with time range
- [ ] Verify locked slots: Yellow badge, disabled, tooltip "Locked: Payment pending"

### Client-Side Validation
- [ ] Select date only → Verify submit button disabled
- [ ] Select date + time → Verify submit button still disabled
- [ ] Select date + time + duration → Verify submit button enabled
- [ ] Click unavailable slot → Verify click does nothing (disabled)

### Server-Side Validation
- [ ] Attempt to book already-booked slot → Verify error: "This time slot is already booked..."
- [ ] Attempt to book locked slot → Verify error: "This time slot is temporarily reserved..."
- [ ] Attempt to book with too-long duration → Verify error: "...Maximum available duration is X hour(s)"

---

## Common Issues

### Issue: Duration dropdown doesn't update when time slot selected

**Solution**: Ensure Alpine.js `fetchAvailableDurations()` is called in time slot click handler. Check browser console for AJAX errors.

### Issue: All slots show as available even when bookings exist

**Solution**: Verify `getAvailabilityForDate()` uses `calculateOccupiedSlots()` method. Check that bookings query includes correct date range and status filter.

### Issue: Submit button remains disabled even with valid selection

**Solution**: Check Alpine.js `isValid()` method. Ensure `availableDurations` array includes the selected duration value (compare as integers).

### Issue: Tooltip doesn't show on disabled slots

**Solution**: Verify `title` attribute is set on button element. Check that booking metadata (range) is passed from controller to view.

---

## Next Steps

After implementing this feature:
1. Run manual testing checklist above
2. Create sample multi-hour bookings for testing
3. Test race condition handling (two users booking same slot simultaneously)
4. Proceed to `/speckit.tasks` to generate detailed task breakdown
5. Begin implementation following task priorities
