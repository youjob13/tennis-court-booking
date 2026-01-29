# Research: Real-Time Booking Validation & Feedback

**Feature**: 003-booking-feedback  
**Created**: 2026-01-28  
**Purpose**: Resolve technical unknowns before design phase

## Research Areas

### 1. Multi-Hour Slot Occupancy Calculation

**Question**: How should the system calculate which hourly slots are occupied by a multi-hour booking?

**Decision**: Calculate consecutive hourly slots based on start_datetime + duration_hours

**Rationale**: 
- Booking model already has `start_datetime` (timestamp) and `duration_hours` (integer) attributes
- A 2 PM booking with 4-hour duration occupies: 2 PM (14:00), 3 PM (15:00), 4 PM (16:00), 5 PM (17:00)
- Formula: For each hour H from 0 to (duration_hours - 1), occupied slot = start_datetime + H hours
- AvailabilityService already generates hourly slots (08:00, 09:00, etc.) in `generateTimeSlots()` method
- Compatible with existing booking validation in `isSlotAvailable()` which checks overlap ranges

**Alternatives considered**:
- Store individual slot records in database → Rejected: Adds complexity; current duration_hours approach is simpler
- Use 30-minute intervals → Rejected: Out of scope (spec assumes hourly slots)

**Implementation approach**:
```php
// In AvailabilityService.php
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

---

### 2. Dynamic Duration Dropdown Validation

**Question**: How should the system determine which duration options are valid for a selected start time?

**Decision**: Calculate available durations by checking consecutive slot availability

**Rationale**:
- User selects start time (e.g., 2 PM)
- System checks if 1-hour booking is valid (2 PM available?) → Show "1 hour" option
- System checks if 2-hour booking is valid (2 PM AND 3 PM available?) → Show "2 hours" option
- Continue up to 8 hours or until consecutive availability breaks
- Prevents user from selecting invalid combinations before form submission

**Alternatives considered**:
- Validate only on form submit → Rejected: Doesn't provide immediate feedback (violates FR-001)
- Show all durations always → Rejected: User could select invalid option, get error after submit

**Implementation approach**:
```php
// In AvailabilityService.php
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

---

### 3. Displaying All Time Slots with Disabled States

**Question**: How should the UI display unavailable slots without hiding them?

**Decision**: Show all slots within operating hours; apply distinct styling to booked/locked/unavailable slots

**Rationale**:
- Current implementation (Feature 001) only shows `available` slots in courts/show.blade.php
- courts/index.blade.php already shows all slots (available/booked/locked) with color-coded badges
- Extend courts/show.blade.php to follow same pattern: display all slots, disable interaction for unavailable
- Use existing Badge component (from Feature 002) with appropriate variants: success (available), secondary (booked), warning (locked)
- Add tooltips/labels to explain WHY slot is disabled (e.g., "Part of 2 PM - 6 PM booking")

**Alternatives considered**:
- Keep current behavior (hide unavailable slots) → Rejected: Violates FR-003 and US3 requirements
- Use strikethrough text → Rejected: Less accessible than disabled button state with clear styling

**Implementation approach**:
```php
// In courts/show.blade.php
@foreach($allSlots as $slot)
    @php
        $slotStatus = $this->getSlotStatus($slot); // 'available', 'booked', 'locked'
    @endphp
    
    <button type="button" 
            class="time-slot {{ $slotStatus !== 'available' ? 'opacity-50 cursor-not-allowed' : '' }}"
            data-slot="{{ $slot }}"
            {{ $slotStatus !== 'available' ? 'disabled' : '' }}>
        <x-badge :variant="$slotStatus === 'available' ? 'success' : ($slotStatus === 'booked' ? 'secondary' : 'warning')">
            {{ $slot }}
        </x-badge>
    </button>
@endforeach
```

---

### 4. Client-Side Validation with Alpine.js

**Question**: How should client-side validation integrate with existing Alpine.js usage?

**Decision**: Extend existing Alpine.js data/methods in courts/show.blade.php booking form

**Rationale**:
- courts/show.blade.php already uses Alpine.js for loading state (`x-data="{ loading: false }"`)
- Add reactive data properties for: selectedDate, selectedTime, selectedDuration, availableDurations
- Add computed property to disable submit button when selection is invalid
- Use Alpine's `x-bind:disabled` to dynamically disable duration options and submit button
- Maintains consistency with existing form behavior

**Alternatives considered**:
- Use Vue.js or React → Rejected: Adds new dependency; Alpine.js is sufficient and already in use
- Pure JavaScript without framework → Rejected: Less maintainable than Alpine.js reactive approach

**Implementation approach**:
```html
<div x-data="{
    selectedDate: '',
    selectedTime: '',
    selectedDuration: '',
    availableDurations: [],
    isValid() {
        return this.selectedDate && this.selectedTime && this.selectedDuration && this.availableDurations.includes(parseInt(this.selectedDuration));
    }
}">
    <select x-model="selectedDuration">
        <template x-for="duration in availableDurations">
            <option :value="duration" x-text="duration + ' hour(s)'"></option>
        </template>
    </select>
    
    <button type="submit" :disabled="!isValid()">
        Proceed to Payment
    </button>
</div>
```

---

### 5. Server-Side Validation Error Messages

**Question**: What specific error messages should the server return for different validation failures?

**Decision**: Use distinct error messages for each failure scenario with actionable guidance

**Rationale**:
- Current BookingController returns generic "Time slot is no longer available" message
- Enhance to distinguish between: slot booked, slot locked, duration too long, outside operating hours
- Helps users understand exactly why booking failed and what alternative actions to take
- Aligns with FR-009 requirement for user-friendly error messages

**Error message mapping**:
- Slot already confirmed → "This time slot is already booked. Please select a different time."
- Slot locked by another user → "This time slot is temporarily reserved by another user. Please wait or select a different time."
- Duration extends beyond available slots → "Selected duration conflicts with existing bookings. Maximum available duration is X hours."
- Outside operating hours → "Booking must be within operating hours (8 AM - 10 PM)."
- Race condition (concurrent booking) → "This slot was just booked by another user. Please select a different time."

**Implementation approach**:
```php
// In BookingController.php store() method
if (!$this->availabilityService->isSlotAvailable($courtId, $datetime, $duration)) {
    // Check specific reason for unavailability
    $conflictingBooking = Booking::where('court_id', $courtId)
        ->where('start_datetime', $datetime)
        ->whereIn('status', ['locked', 'confirmed'])
        ->first();
    
    if ($conflictingBooking && $conflictingBooking->status === 'confirmed') {
        return back()->withErrors(['start_datetime' => 'This time slot is already booked. Please select a different time.']);
    } elseif ($conflictingBooking && $conflictingBooking->status === 'locked') {
        return back()->withErrors(['start_datetime' => 'This time slot is temporarily reserved. Please wait or select a different time.']);
    }
    
    // Check if duration is too long
    $maxAvailableDuration = $this->availabilityService->getAvailableDurationsForSlot($courtId, $datetime);
    if (!empty($maxAvailableDuration)) {
        $maxDuration = max($maxAvailableDuration);
        return back()->withErrors(['duration_hours' => "Selected duration conflicts with existing bookings. Maximum available duration is $maxDuration hour(s)."]);
    }
}
```

---

### 6. Timezone Handling Strategy

**Question**: How should the system handle timezone conversion for bookings and display?

**Decision**: Store UTC in database, display in browser's local timezone using JavaScript Intl.DateTimeFormat API

**Rationale**:
- Laravel's Carbon library and timestampTz column type handle UTC storage natively
- Booking model's start_datetime attribute already uses Carbon (timezone-aware)
- JavaScript Intl.DateTimeFormat API is built into all modern browsers (IE11+)
- No additional dependencies required (no Moment.js, date-fns, etc.)
- Browser automatically detects user's timezone
- Format: "h:mm A z" produces "2:00 PM EST" (12-hour with AM/PM and timezone abbreviation)
- Compatible with existing codebase: Laravel outputs ISO 8601 timestamps in JSON responses, JavaScript Date constructor parses them correctly

**Alternatives considered**:
- Require users to set timezone in profile → Rejected: Adds configuration burden; browser detection is automatic and more accurate
- Store local timezone in database → Rejected: Creates timezone conversion complexity; UTC is industry standard
- 24-hour format without timezone → Rejected: Less familiar to users; timezone indicator prevents confusion

**Implementation approach**:
```javascript
// Utility function for formatting times in user's local timezone
function formatLocalTime(utcDatetimeString) {
    const date = new Date(utcDatetimeString); // Parse UTC timestamp from server
    const formatter = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        timeZoneName: 'short'
    });
    return formatter.format(date); // Returns "2:00 PM EST"
}

// Usage in Blade template
<script>
    const availability = @json($availability); // Contains UTC timestamps
    
    // Convert all times to local timezone for display
    Object.keys(availability).forEach(date => {
        availability[date].available = availability[date].available.map(slot => {
            return formatLocalTime(slot);
        });
    });
</script>
```

**Server-side (Laravel)**:
- Continue storing timestamps in UTC (no changes needed)
- Carbon automatically converts to UTC when saving
- When outputting to JSON, Laravel includes timezone offset (ISO 8601 format)

---

### 7. Past Time Slot Display

**Question**: How should the system display time slots that have already passed for the current day?

**Decision**: Show all slots but disable past ones with "Time has passed" message

**Rationale**:
- Maintains visual consistency (all slots within operating hours are visible)
- Helps users understand court operating hours even for past times
- Aligns with FR-003 requirement to "display all time slots within court operating hours"
- Disabled state with clear message prevents confusion (users know why they can't select)
- Better UX than hiding slots (transparency about what times existed earlier today)

**Alternatives considered**:
- Hide past slots entirely → Rejected: Violates FR-003; creates inconsistent UI (different number of slots throughout the day)
- Hide slots where start time + 1 hour has passed → Rejected: More complex logic; same transparency issues
- Show only if at least 30 minutes remain → Rejected: Adds complexity without clear user benefit

**Implementation approach**:
```javascript
// In updateTimeSlots() function (courts/show.blade.php)
function isPastSlot(slotDatetime) {
    const slotTime = new Date(slotDatetime);
    const now = new Date();
    return slotTime < now;
}

const slotsHtml = allSlots.map(slot => {
    const isPast = isPastSlot(slot.datetime);
    const isAvailable = slot.status === 'available' && !isPast;
    
    const disabledClass = !isAvailable ? 'opacity-50 cursor-not-allowed' : '';
    const disabledAttr = !isAvailable ? 'disabled' : '';
    
    let tooltip = slot.tooltip || '';
    if (isPast && slot.status === 'available') {
        tooltip = 'Time has passed';
    }
    
    return `
        <button type="button"
                class="time-slot ${disabledClass}"
                title="${tooltip}"
                ${disabledAttr}>
            ${formatLocalTime(slot.datetime)}
        </button>
    `;
});
```

**Visual distinction**:
- Past available slots: Gray badge (same as booked), disabled, tooltip "Time has passed"
- Past booked/locked slots: Same styling as future booked/locked (no special handling needed)

---

### 6. Timezone Handling Strategy

**Question**: How should the system handle timezone conversion for bookings and display?

**Decision**: Store UTC in database, display in browser's local timezone using JavaScript Intl.DateTimeFormat API

**Rationale**:
- Laravel's Carbon library and timestampTz column type handle UTC storage natively
- Booking model's start_datetime attribute already uses Carbon (timezone-aware)
- JavaScript Intl.DateTimeFormat API is built into all modern browsers (IE11+)
- No additional dependencies required (no Moment.js, date-fns, etc.)
- Browser automatically detects user's timezone
- Format: "h:mm A z" produces "2:00 PM EST" (12-hour with AM/PM and timezone abbreviation)
- Compatible with existing codebase: Laravel outputs ISO 8601 timestamps in JSON responses, JavaScript Date constructor parses them correctly

**Alternatives considered**:
- Require users to set timezone in profile → Rejected: Adds configuration burden; browser detection is automatic and more accurate
- Store local timezone in database → Rejected: Creates timezone conversion complexity; UTC is industry standard
- 24-hour format without timezone → Rejected: Less familiar to users; timezone indicator prevents confusion

**Implementation approach**:
```javascript
// Utility function for formatting times in user's local timezone
function formatLocalTime(utcDatetimeString) {
    const date = new Date(utcDatetimeString); // Parse UTC timestamp from server
    const formatter = new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        timeZoneName: 'short'
    });
    return formatter.format(date); // Returns "2:00 PM EST"
}

// Usage in Blade template
<script>
    const availability = @json($availability); // Contains UTC timestamps
    
    // Convert all times to local timezone for display
    Object.keys(availability).forEach(date => {
        availability[date].available = availability[date].available.map(slot => {
            return formatLocalTime(slot);
        });
    });
</script>
```

**Server-side (Laravel)**:
- Continue storing timestamps in UTC (no changes needed)
- Carbon automatically converts to UTC when saving
- When outputting to JSON, Laravel includes timezone offset (ISO 8601 format)

---

### 7. Past Time Slot Display

**Question**: How should the system display time slots that have already passed for the current day?

**Decision**: Show all slots but disable past ones with "Time has passed" message

**Rationale**:
- Maintains visual consistency (all slots within operating hours are visible)
- Helps users understand court operating hours even for past times
- Aligns with FR-003 requirement to "display all time slots within court operating hours"
- Disabled state with clear message prevents confusion (users know why they can't select)
- Better UX than hiding slots (transparency about what times existed earlier today)

**Alternatives considered**:
- Hide past slots entirely → Rejected: Violates FR-003; creates inconsistent UI (different number of slots throughout the day)
- Hide slots where start time + 1 hour has passed → Rejected: More complex logic; same transparency issues
- Show only if at least 30 minutes remain → Rejected: Adds complexity without clear user benefit

**Implementation approach**:
```javascript
// In updateTimeSlots() function (courts/show.blade.php)
function isPastSlot(slotDatetime) {
    const slotTime = new Date(slotDatetime);
    const now = new Date();
    return slotTime < now;
}

const slotsHtml = allSlots.map(slot => {
    const isPast = isPastSlot(slot.datetime);
    const isAvailable = slot.status === 'available' && !isPast;
    
    const disabledClass = !isAvailable ? 'opacity-50 cursor-not-allowed' : '';
    const disabledAttr = !isAvailable ? 'disabled' : '';
    
    let tooltip = slot.tooltip || '';
    if (isPast && slot.status === 'available') {
        tooltip = 'Time has passed';
    }
    
    return `
        <button type="button"
                class="time-slot ${disabledClass}"
                title="${tooltip}"
                ${disabledAttr}>
            ${formatLocalTime(slot.datetime)}
        </button>
    `;
});
```

**Visual distinction**:
- Past available slots: Gray badge (same as booked), disabled, tooltip "Time has passed"
- Past booked/locked slots: Same styling as future booked/locked (no special handling needed)

---

### 8. Slot Status Tooltips/Labels

**Question**: How should the UI communicate WHY a slot is disabled?

**Decision**: Use HTML title attribute for browser tooltips + visible badge text for locked/booked status; include timezone in time ranges

**Rationale**:
- Simple implementation: Add `title` attribute to disabled slot buttons
- Accessible: Works with keyboard navigation and screen readers
- For booked slots: Show booking time range with timezone (e.g., "Booked: 2:00 PM - 6:00 PM EST")
- For locked slots: Show status (e.g., "Locked: Payment pending")
- For past slots: Show "Time has passed"
- Badge component (Feature 002) already displays status text; tooltip adds time range context
- Timezone abbreviation (once after range) provides clarity without cluttering the tooltip

**Alternatives considered**:
- Custom JavaScript tooltip library → Rejected: Adds dependency; browser tooltips are sufficient
- Always-visible labels under each slot → Rejected: Clutters UI; tooltips provide info on demand
- No timezone in tooltip → Rejected: Users in different timezones need context

**Implementation approach**:
```php
<button type="button" 
        class="time-slot"
        data-slot="{{ $slot }}"
        title="@if($status === 'booked') Booked: {{ formatLocalTime($booking->start_datetime) }} - {{ formatLocalTime($booking->end_datetime) }} @elseif($status === 'locked') Locked: Payment pending @elseif($isPast) Time has passed @endif"
        {{ $status !== 'available' || $isPast ? 'disabled' : '' }}>
    <x-badge :variant="...">{{ formatLocalTime($slot) }}</x-badge>
</button>
```

---

## Summary

All technical unknowns resolved:
1. **Multi-hour occupancy**: Calculate consecutive hourly slots from start_datetime + duration_hours
2. **Dynamic duration validation**: Check consecutive slot availability, show only valid durations
3. **Disabled slot display**: Show all slots with distinct styling (Badge component variants) + disabled state
4. **Client-side validation**: Extend existing Alpine.js reactive data with dynamic duration array and validation
5. **Error messages**: Specific, actionable messages for each failure scenario (booked/locked/conflict/hours)
6. **Timezone handling**: Store UTC in database, display in user's browser timezone using JavaScript Intl.DateTimeFormat API
7. **Past slot display**: Show all slots including past ones, disable with "Time has passed" message
8. **Tooltips**: HTML title attribute with booking time range (including timezone), lock status, or "Time has passed"

**Ready for Phase 1**: All decisions align with existing Laravel/Blade/Alpine.js/Tailwind architecture. No new dependencies required (Intl API is native to browsers).
