# Feature Specification: Real-Time Booking Validation & Feedback

**Feature Branch**: `003-booking-feedback`  
**Created**: 2026-01-28  
**Status**: Draft  
**Input**: User description: "Improve booking system feedback: validate slots before booking, disable multi-hour slot ranges, show disabled slots in UI"

## Clarifications

### Session 2026-01-28

- Q: How should timezones be handled for bookings and slot display? → A: Store UTC in database, display in browser's local timezone using JavaScript (Intl.DateTimeFormat API)
- Q: How should the system handle past time slots (e.g., current time is 2:02 PM - should 1:00 PM and 2:00 PM slots be shown)? → A: Show all slots but disable past ones with "Time has passed" message
- Q: When displaying times in the user's local timezone, what format should be shown? → A: 12-hour format with AM/PM and timezone abbreviation (e.g., "2:00 PM EST")
- Q: When showing tooltips for disabled slots that are part of a multi-hour booking, should the timezone be included? → A: Show timezone once after the range (e.g., "Booked: 2:00 PM - 6:00 PM EST")
- Q: Which JavaScript approach should be used for timezone conversion? → A: Native JavaScript Intl.DateTimeFormat API (no additional library required)

## User Scenarios & Testing *(mandatory)*

<!--
  IMPORTANT: User stories should be PRIORITIZED as user journeys ordered by importance.
  Each user story/journey must be INDEPENDENTLY TESTABLE - meaning if you implement just ONE of them,
  you should still have a viable MVP (Minimum Viable Product) that delivers value.
  
  Assign priorities (P1, P2, P3, etc.) to each story, where P1 is the most critical.
  Think of each story as a standalone slice of functionality that can be:
  - Developed independently
  - Tested independently
  - Deployed independently
  - Demonstrated to users independently
-->

### User Story 1 - Pre-Booking Slot Validation (Priority: P1)

Users receive immediate feedback about slot availability BEFORE attempting to book, preventing frustration from failed booking attempts after selecting date/time/duration.

**Why this priority**: This is the core improvement that prevents the most common user frustration - discovering a slot isn't available only after going through the booking form. This directly impacts conversion rate and user satisfaction.

**Independent Test**: Select a court, choose a date/time that conflicts with an existing booking (or lock), and verify that the booking button is disabled with a clear message explaining why, without requiring form submission.

**Acceptance Scenarios**:

1. **Given** user is on court detail page, **When** they select a start time that overlaps with an existing confirmed booking, **Then** the booking form shows a disabled submit button with message "Selected time slot is already booked"
2. **Given** user is on court detail page, **When** they select a start time that overlaps with a locked booking (payment pending), **Then** the booking form shows a disabled submit button with message "Selected time slot is temporarily locked"
3. **Given** user selects a valid start time, **When** they change duration to a value that would overlap with an existing booking, **Then** the duration dropdown disables conflicting options and shows only valid durations
4. **Given** user is viewing the booking form, **When** another user locks the same slot in real-time, **Then** the first user's form updates immediately to show the slot as unavailable (if real-time refresh is implemented), or shows server-side error on submit attempt

---

### User Story 2 - Multi-Hour Booking Slot Range Display (Priority: P1)

When users book a multi-hour slot (e.g., 2 PM for 4 hours), the system visually disables all affected time slots (2 PM, 3 PM, 4 PM, 5 PM) in the UI to clearly communicate unavailability.

**Why this priority**: Critical for preventing double-booking confusion. Users need to see at a glance which consecutive slots are blocked by a single booking. This is equally important as pre-validation since it affects how users understand availability.

**Independent Test**: Create a booking for 2 PM with 4-hour duration, then view the court listing page. Verify that time slots 2 PM, 3 PM, 4 PM, and 5 PM all show as unavailable/booked, not just the 2 PM start time.

**Acceptance Scenarios**:

1. **Given** a booking exists for 2 PM with 4-hour duration, **When** user views court availability on the listing page, **Then** time slots 2 PM, 3 PM, 4 PM, and 5 PM all display as booked/unavailable
2. **Given** a locked booking (payment pending) exists for 10 AM with 2-hour duration, **When** user views court availability, **Then** time slots 10 AM and 11 AM display as locked/unavailable
3. **Given** user is on court detail page, **When** they select a start time and duration, **Then** the UI immediately shows which subsequent slots would be blocked by this booking
4. **Given** a multi-hour booking is cancelled, **When** user refreshes the court listing, **Then** all previously blocked slots (2 PM, 3 PM, 4 PM, 5 PM) now show as available

---

### User Story 3 - Always Display All Time Slots (Priority: P2)

Users always see all time slots (within operating hours) on the court listing and detail pages, with unavailable slots shown in a disabled/grayed-out state rather than hidden entirely.

**Why this priority**: Improves transparency and helps users understand the full availability picture. Hiding slots creates confusion about operating hours and makes it harder to plan alternative times. This is slightly lower priority than P1 stories but still important for UX clarity.

**Independent Test**: View a court that has several booked slots throughout the day. Verify that all hourly slots from opening to closing time are displayed, with booked/locked slots shown in a disabled state with visual distinction from available slots.

**Acceptance Scenarios**:

1. **Given** a court operates from 8 AM to 10 PM, **When** user views the court listing, **Then** all hourly slots from 8 AM to 9 PM are displayed (last slot starts at 9 PM for 1-hour minimum)
2. **Given** several slots are booked/locked, **When** user views availability, **Then** unavailable slots are shown with disabled styling (e.g., gray background, reduced opacity, strikethrough text) rather than hidden
3. **Given** user hovers over a disabled slot, **When** the slot is part of a confirmed booking, **Then** a tooltip or label shows "Booked: 2 PM - 6 PM" to explain the unavailability
4. **Given** user hovers over a disabled slot, **When** the slot is part of a locked booking, **Then** a tooltip shows "Locked: Payment pending" with expiration time

---

### Edge Cases

- What happens when a lock expires while user is viewing the court page? → UI should auto-refresh or show server-side error on submit; previously locked slots become available
- How does system handle race conditions when two users select the same slot simultaneously? → Server-side validation with database locks; first submit succeeds, second gets immediate error "This slot was just booked"
- What happens when user selects a start time, then the duration dropdown has no valid options (all would conflict)? → Duration dropdown shows "No available durations" message, booking button remains disabled
- How does system handle bookings that span across midnight (e.g., 10 PM for 4 hours)? → Prevent bookings beyond operating hours using duration dropdown constraints
- What happens when court operating hours change and existing bookings become invalid? → Out of scope for this feature; assume operating hours don't change retroactively
- How does the system handle partial overlaps (e.g., booking 2 PM for 3 hours when 4 PM is already booked)? → Duration dropdown disables 3-hour and 4-hour options, only shows 1-hour and 2-hour as valid
- What happens when user in different timezone views availability? → All times displayed in user's browser timezone; server validates booking requests against UTC timestamps to prevent confusion
- What if user's system clock is wrong? → Server-side validation always uses server time (UTC); client-side "past slot" filtering is advisory only (server enforces "after:now" validation)
- How are past slots for today handled at midnight boundary? → At 12:00 AM local time, all slots from previous day are past; new day shows all future slots as available (subject to bookings)

## Requirements *(mandatory)*

<!--
  ACTION REQUIRED: The content in this section represents placeholders.
  Fill them out with the right functional requirements.
-->

### Functional Requirements

- **FR-001**: System MUST validate slot availability client-side before form submission by checking available slots against selected start time and duration
- **FR-002**: System MUST validate slot availability server-side on booking submission to prevent race conditions, returning specific error messages for conflicts
- **FR-003**: System MUST display all time slots within court operating hours on listing and detail pages, showing unavailable slots in disabled state rather than hiding them
- **FR-004**: When user selects a start time on the booking form, system MUST calculate and disable duration dropdown options that would create overlapping bookings
- **FR-005**: System MUST visually represent multi-hour bookings by marking all affected consecutive time slots as unavailable (not just the start time)
- **FR-006**: System MUST provide clear feedback messages explaining WHY a slot is unavailable: "Booked", "Locked (payment pending)", or "Part of [start time] - [end time] booking"
- **FR-007**: System MUST disable the booking form submit button when selected time/duration combination is invalid, with explanatory message
- **FR-008**: System MUST handle race conditions by using database-level locks during booking creation, ensuring atomic slot allocation
- **FR-009**: System MUST return user-friendly error messages when server-side validation fails (e.g., "This slot was just booked by another user. Please select a different time.")
- **FR-010**: System MUST recalculate available durations dynamically when user changes start time on booking form
- **FR-011**: Disabled time slots MUST be visually distinct from available slots using gray background, reduced opacity, or strikethrough styling
- **FR-012**: System MUST display tooltip or inline message on disabled slots showing the conflicting booking's time range (e.g., "Booked: 2 PM - 6 PM")
- **FR-013**: System MUST store all booking datetimes in UTC timezone in the database for consistency across timezones
- **FR-014**: System MUST display all times in the user's browser local timezone using JavaScript Intl.DateTimeFormat API with format "h:mm A z" (e.g., "2:00 PM EST")
- **FR-015**: System MUST show all time slots (including past slots for today) but disable past slots with visual indication and tooltip message "Time has passed"
- **FR-016**: System MUST convert slot times to user's local timezone when filtering/displaying availability, ensuring "past slot" logic uses browser's current time

### Key Entities

- **Booking**: Represents a confirmed or locked reservation with start_datetime and duration_hours attributes; conflicts calculated by checking if [start_datetime, start_datetime + duration_hours) ranges overlap
- **TimeSlot (Calculated)**: Not stored in database; derived from court operating hours and existing bookings; represents one hourly increment with status (available, booked, locked, disabled)
- **AvailabilityService**: Service class responsible for calculating available time slots given a court, date, start time, and duration; checks for conflicts with existing bookings

## Success Criteria *(mandatory)*

<!--
  ACTION REQUIRED: Define measurable success criteria.
  These must be technology-agnostic and measurable.
-->

### Measurable Outcomes

- **SC-001**: Users receive immediate visual feedback (within 100ms) when selecting an unavailable time slot, without requiring form submission
- **SC-002**: Booking form submit button is disabled 100% of the time when selected slot is unavailable, preventing failed booking attempts
- **SC-003**: When viewing court availability, all time slots within operating hours are visible, with unavailable slots clearly distinguished from available ones
- **SC-004**: Multi-hour bookings correctly disable all affected consecutive slots (e.g., 4-hour booking disables 4 hourly slots) on both listing and detail pages
- **SC-005**: Race condition handling succeeds 100% of the time - no double-bookings occur even under concurrent user submissions
- **SC-006**: Users can identify WHY a slot is disabled through visual cues or tooltips (booked vs locked vs part of multi-hour booking)
- **SC-007**: Duration dropdown dynamically updates to show only valid options (no conflicts) when start time changes
- **SC-008**: Server-side validation errors provide actionable feedback messages that help users select alternative times
- **SC-009**: All times displayed in UI reflect user's browser timezone with clear timezone indicator (e.g., "EST", "PST")
- **SC-010**: Past time slots are visually distinguished from future unavailable slots (e.g., different styling or explicit "Time has passed" label)

## Assumptions

- Booking system already has AvailabilityService or similar service for checking slot conflicts (referenced in existing codebase)
- Database supports transaction-level locking to prevent race conditions (Laravel uses database transactions)
- Frontend uses Alpine.js for reactive form behavior (consistent with existing implementation)
- Court operating hours are fixed and don't change dynamically (or if they do, that's handled separately)
- Minimum booking duration is 1 hour (based on existing system behavior)
- Time slots are hourly increments (8 AM, 9 AM, 10 AM, etc.)
- Lock expiration mechanism already exists (15-minute lock duration per existing feature)
- UI already displays time slot badges on court listing page (can be extended to show disabled state)
- Laravel stores timestamps in UTC by default (using timestampTz column type or Carbon library)
- Modern browsers support Intl.DateTimeFormat API (IE11+, all evergreen browsers)
- Users' browsers have JavaScript enabled (required for Alpine.js and timezone conversion)
- Court operating hours are defined in a timezone-agnostic format (e.g., "08:00-22:00" applies to court's local timezone)

## Out of Scope

- Real-time WebSocket updates for live availability changes (use page refresh or periodic AJAX polling instead)
- Booking modification or rescheduling (separate feature)
- Partial-hour bookings (e.g., 2:30 PM start time or 1.5-hour duration)
- Calendar view for selecting dates (use existing date picker)
- Email notifications when slot becomes available
- Waitlist or queue system for fully booked courts
- Admin override to book unavailable slots
- Cross-court availability search ("show me any court available at 2 PM")
- Historical booking analytics or reporting
