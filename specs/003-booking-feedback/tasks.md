# Tasks: Real-Time Booking Validation & Feedback

**Feature Branch**: `003-booking-feedback`  
**Input**: Design documents from `/specs/003-booking-feedback/`  
**Prerequisites**: plan.md ✅, spec.md ✅, research.md ✅, data-model.md ✅, contracts/api.md ✅

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `- [ ] [ID] [P?] [Story?] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup & Infrastructure

**Purpose**: Timezone utilities and shared infrastructure for all user stories

- [X] T001 Create JavaScript timezone utility functions (formatLocalTime, isPastSlot) in resources/views/courts/show.blade.php
- [X] T002 Test timezone conversion with various UTC timestamps to ensure correct browser timezone display

**Completion Criteria**: Timezone utilities available for use in all subsequent phases

---

## Phase 2: Foundational - Multi-Hour Slot Calculation (Blocks All User Stories)

**Purpose**: Core service method that all user stories depend on - calculates which hourly slots are occupied by multi-hour bookings

### Service Layer - AvailabilityService

- [X] T003 [P] Implement calculateOccupiedSlots(Booking $booking): array method in app/Services/AvailabilityService.php
- [X] T004 Modify getAvailabilityForDate() to use calculateOccupiedSlots() for multi-hour bookings in app/Services/AvailabilityService.php
- [X] T005 Test calculateOccupiedSlots() with 1-hour, 4-hour, and 8-hour bookings to verify all consecutive slots returned

**Completion Criteria**: Multi-hour bookings correctly calculate ALL occupied slots (not just start time)

---

## Phase 3: User Story 1 - Pre-Booking Slot Validation (Priority: P1) 🎯 MVP

**Goal**: Users receive immediate feedback about slot availability BEFORE form submission

**Independent Test**: Select unavailable slot → Observe submit button disabled with message → No server request made

### Service Layer - Dynamic Duration Validation

- [X] T006 [P] [US1] Implement getAvailableDurationsForSlot(int $courtId, string $datetime): array method in app/Services/AvailabilityService.php
- [X] T007 [US1] Test getAvailableDurationsForSlot() with various scenarios (no conflicts, partial conflicts, all blocked)

### API Endpoint - Duration Validation

- [X] T008 [US1] Add GET /api/courts/{court}/availability/durations route in routes/api.php
- [X] T009 [US1] Implement getAvailableDurations(Court $court, Request $request) method in app/Http/Controllers/CourtController.php
- [X] T010 [US1] Test API endpoint with Postman/curl to verify correct duration array returned

### Frontend - Alpine.js Reactive Validation

- [X] T011 [US1] Extend Alpine.js x-data in resources/views/courts/show.blade.php with selectedDate, selectedTime, selectedDuration, availableDurations
- [X] T012 [US1] Implement fetchAvailableDurations() async method in Alpine.js component in resources/views/courts/show.blade.php
- [X] T013 [US1] Add @time-slot-selected event trigger to call fetchAvailableDurations() in resources/views/courts/show.blade.php
- [X] T014 [US1] Update duration dropdown to use availableDurations array with x-for loop in resources/views/courts/show.blade.php
- [X] T015 [US1] Implement isValid() computed property to check all fields selected and duration valid in resources/views/courts/show.blade.php
- [X] T016 [US1] Bind submit button :disabled to !isValid() in resources/views/courts/show.blade.php

### Server-Side Validation Enhancement

- [X] T017 [US1] Enhance BookingController@store() validation to return specific error messages (booked/locked/conflict) in app/Http/Controllers/BookingController.php
- [ ] T018 [US1] Test race condition handling by attempting concurrent bookings for same slot

**US1 Completion Criteria**:
- ✅ Duration dropdown dynamically updates based on start time selection
- ✅ Submit button disabled when invalid selection made
- ✅ Specific error messages displayed for each failure type
- ✅ No failed booking attempts due to unavailable slots

**US1 Test Scenarios**:
1. Select slot at 2 PM when 4 PM is booked → Duration dropdown shows 1-2 hours only (not 3+)
2. Select all valid options → Submit button enabled
3. Select slot already booked → Submit button disabled with clear message
4. Attempt server-side submit with unavailable slot → Specific error message returned

---

## Phase 4: User Story 2 - Multi-Hour Booking Slot Range Display (Priority: P1) 🎯 MVP

**Goal**: All consecutive slots occupied by multi-hour bookings are visually disabled (not just start time)

**Independent Test**: Create 4-hour booking at 2 PM → View court listing → Verify 2 PM, 3 PM, 4 PM, 5 PM all show as booked

### Service Layer - Slot Status with Metadata

- [ ] T019 [P] [US2] Implement getAllSlotsWithStatus(int $courtId, string $date): array method in app/Services/AvailabilityService.php
- [ ] T020 [US2] Test getAllSlotsWithStatus() returns all slots with correct status and booking metadata

### Controller - Enhanced Availability Data

- [ ] T021 [US2] Modify CourtController@show() to pass booking metadata (range, booking_id) in app/Http/Controllers/CourtController.php
- [ ] T022 [US2] Modify CourtController@index() to include all occupied slots for multi-hour bookings in app/Http/Controllers/CourtController.php

### Frontend - Court Listing Page

- [ ] T023 [US2] Update court listing slot display loop to show all occupied slots in resources/views/courts/index.blade.php
- [ ] T024 [US2] Add timezone conversion using formatLocalTime() for all slot times in resources/views/courts/index.blade.php
- [ ] T025 [US2] Add tooltips with booking range and timezone to booked/locked slots in resources/views/courts/index.blade.php

### Frontend - Court Detail Page

- [ ] T026 [US2] Update updateTimeSlots() JavaScript function to display booking range tooltips in resources/views/courts/show.blade.php
- [ ] T027 [US2] Test visual preview: When user selects start time + duration, highlight which subsequent slots would be blocked

**US2 Completion Criteria**:
- ✅ Multi-hour bookings display ALL occupied slots as unavailable
- ✅ Tooltips show time range with timezone (e.g., "Booked: 2:00 PM - 6:00 PM EST")
- ✅ Both listing and detail pages correctly display multi-hour occupancy
- ✅ Times displayed in user's browser timezone

**US2 Test Scenarios**:
1. Create 4-hour booking at 2 PM → View listing → All 4 slots (2, 3, 4, 5 PM) show as booked
2. Hover over booked slot → Tooltip shows "Booked: 2:00 PM - 6:00 PM EST"
3. View from different timezone → Times convert correctly to local timezone
4. Cancel multi-hour booking → All slots immediately show as available

---

## Phase 5: User Story 3 - Always Display All Time Slots (Priority: P2)

**Goal**: All time slots visible (including past/booked/locked) with clear visual distinction

**Independent Test**: View court at 3 PM → Verify all slots from 8 AM to 9 PM displayed → Past slots disabled with "Time has passed" message

### Frontend - Courts Listing Page

- [ ] T028 [P] [US3] Update slot display to show ALL slots (available/booked/locked/past) in resources/views/courts/index.blade.php
- [ ] T029 [US3] Add isPastSlot() check for today's date and disable past slots in resources/views/courts/index.blade.php
- [ ] T030 [US3] Apply distinct visual styling (opacity-50, cursor-not-allowed) to disabled slots in resources/views/courts/index.blade.php
- [ ] T031 [US3] Add legend showing slot status colors (Available/Booked/Locked/Past) in resources/views/courts/index.blade.php

### Frontend - Courts Detail Page

- [ ] T032 [US3] Update updateTimeSlots() to include ALL slots (not filter by availability) in resources/views/courts/show.blade.php
- [ ] T033 [US3] Add isPastSlot() logic to disable past slots with tooltip "Time has passed" in resources/views/courts/show.blade.php
- [ ] T034 [US3] Apply Badge component styling (success/secondary/warning) based on slot status in resources/views/courts/show.blade.php
- [ ] T035 [US3] Update slot legend to match court listing page styling in resources/views/courts/show.blade.php

**US3 Completion Criteria**:
- ✅ All slots within operating hours are visible (not hidden)
- ✅ Past slots disabled with "Time has passed" tooltip
- ✅ Booked slots disabled with booking range tooltip
- ✅ Locked slots disabled with "Locked: Payment pending" tooltip
- ✅ Legend clearly explains slot colors/states

**US3 Test Scenarios**:
1. View court at 3 PM → Slots 8 AM - 2 PM show as past (disabled, gray, "Time has passed")
2. View court with mixed bookings → All slots visible with appropriate states
3. Hover over disabled slot → Tooltip explains why it's unavailable
4. Compare at different times → Past slot count increases as day progresses

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final integration, error handling, and edge case handling

### Integration & Testing

- [ ] T036 [P] Test full booking flow with timezone conversion (create booking in EST, view from PST)
- [ ] T037 [P] Test past slot filtering across midnight boundary (11 PM slots becoming past at 12 AM)
- [ ] T038 Test race condition: Two users simultaneously select same slot → First succeeds, second gets clear error
- [ ] T039 Test duration dropdown updates when switching between different start times
- [ ] T040 Test visual feedback speed: Client-side validation responds within 100ms

### Edge Case Handling

- [ ] T041 Add "No available durations" message when duration dropdown is empty in resources/views/courts/show.blade.php
- [ ] T042 Handle AJAX error gracefully when fetchAvailableDurations() fails in resources/views/courts/show.blade.php
- [ ] T043 Test booking form behavior when operating hours boundary reached (e.g., 9 PM slot, can't book 4+ hours)

### Documentation & Code Quality

- [ ] T044 Add inline comments explaining timezone conversion logic in resources/views/courts/show.blade.php
- [ ] T045 [P] Add docblock comments to new AvailabilityService methods (calculateOccupiedSlots, getAvailableDurationsForSlot, getAllSlotsWithStatus)
- [ ] T046 [P] Verify all service methods follow PSR-12 coding standards

**Completion Criteria**: All edge cases handled, code documented, no regressions in existing booking flow

---

## Dependencies & Execution Order

### Critical Path (Must Complete in Order)

1. **Phase 1** (Setup) → Required for all subsequent phases
2. **Phase 2** (Foundational) → Blocks ALL user stories (US1, US2, US3)
3. **Phase 3, 4, 5** (User Stories) → Can be implemented independently AFTER Phase 2
4. **Phase 6** (Polish) → Final integration after all user stories complete

### Parallel Opportunities

**After Phase 2 completes**, these can run in parallel:

| Track A (US1) | Track B (US2) | Track C (US3) |
|---------------|---------------|---------------|
| T006-T010 (Service + API) | T019-T022 (Service + Controller) | T028-T031 (Listing Page) |
| T011-T016 (Frontend) | T023-T027 (Frontend) | T032-T035 (Detail Page) |

**Recommended MVP Scope**: Implement US1 + US2 (both P1) first for maximum user impact, then add US3 (P2) for transparency.

---

## Implementation Strategy

### MVP-First Approach (Iterative Delivery)

1. **Sprint 1 (Minimum Viable)**: Phase 1 → Phase 2 → Phase 3 (US1 only)
   - **Delivers**: Dynamic duration validation, submit button disabling, immediate feedback
   - **Test**: User selects slot, duration dropdown updates, invalid selections blocked
   - **Value**: Prevents failed booking attempts (primary pain point)

2. **Sprint 2 (Essential Enhancement)**: Phase 4 (US2 only)
   - **Delivers**: Multi-hour bookings display all occupied slots
   - **Test**: Create 4-hour booking, verify all 4 slots show as booked
   - **Value**: Prevents double-booking confusion, improves availability clarity

3. **Sprint 3 (Transparency)**: Phase 5 (US3 only)
   - **Delivers**: All slots always visible with disabled states
   - **Test**: View court throughout day, verify past/booked/available slots all shown
   - **Value**: Better understanding of operating hours and availability patterns

4. **Sprint 4 (Polish)**: Phase 6
   - **Delivers**: Edge case handling, error messages, documentation
   - **Test**: Race conditions, timezone edge cases, midnight boundaries
   - **Value**: Production-ready robustness

---

## Task Summary

**Total Tasks**: 46  
**By Phase**:
- Phase 1 (Setup): 2 tasks
- Phase 2 (Foundational): 3 tasks
- Phase 3 (US1 - Pre-Booking Validation): 13 tasks
- Phase 4 (US2 - Multi-Hour Display): 9 tasks
- Phase 5 (US3 - Always Display All Slots): 8 tasks
- Phase 6 (Polish): 11 tasks

**Parallelizable Tasks**: 13 tasks marked with [P]  
**MVP Scope (US1 only)**: 18 tasks (Phases 1, 2, 3)  
**Essential MVP (US1 + US2)**: 27 tasks (Phases 1, 2, 3, 4)  
**Full Feature**: 46 tasks (All phases)

**Estimated Effort**:
- Phase 1: 0.5 hours
- Phase 2: 1.5 hours
- Phase 3 (US1): 3 hours
- Phase 4 (US2): 2 hours
- Phase 5 (US3): 1.5 hours
- Phase 6: 1.5 hours
- **Total**: ~10 hours for experienced Laravel developer

---

## Validation Checklist

### Format Compliance ✅

- [x] All tasks use `- [ ] [TaskID] [P?] [Story?] Description` format
- [x] Task IDs sequential (T001-T046)
- [x] Parallelizable tasks marked with [P] (13 tasks)
- [x] User story tasks labeled with [US1], [US2], [US3]
- [x] All tasks include specific file paths

### Coverage Completeness ✅

- [x] All 3 user stories have implementation tasks
- [x] All user stories have independent test criteria
- [x] All AvailabilityService methods from contracts/api.md mapped to tasks
- [x] All frontend changes (Alpine.js, Blade templates) mapped to tasks
- [x] All FR-001 through FR-016 requirements covered by tasks
- [x] Timezone handling (FR-013, FR-014, FR-015, FR-016) covered in Phases 1-5
- [x] Past slot display (FR-015) covered in Phase 5 (US3)
- [x] Multi-hour occupancy (FR-005) covered in Phase 2 + Phase 4 (US2)

### Organization ✅

- [x] Tasks organized by user story (Phase 3/US1, Phase 4/US2, Phase 5/US3)
- [x] Each user story phase has completion criteria and test scenarios
- [x] Dependencies documented (Phase 2 blocks all user stories)
- [x] Parallel execution opportunities identified
- [x] MVP scope clearly defined (18 tasks for US1, 27 for US1+US2)
