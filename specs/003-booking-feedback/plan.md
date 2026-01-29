# Implementation Plan: Real-Time Booking Validation & Feedback

**Branch**: `003-booking-feedback` | **Date**: 2026-01-28 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/003-booking-feedback/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Enhance the booking system to provide immediate user feedback by:
1. Validating slot availability client-side before form submission (prevent wasted booking attempts)
2. Visually disabling ALL affected consecutive time slots for multi-hour bookings (e.g., 2 PM for 4 hours disables 2, 3, 4, 5 PM)
3. Always displaying all time slots with clear disabled/booked states instead of hiding them (improve transparency)

Technical approach: Extend existing AvailabilityService to calculate multi-hour slot occupancy, enhance Alpine.js-based booking form with dynamic duration validation, update Blade templates to show all slots with distinct visual states (available/booked/locked), and strengthen server-side validation with improved error messages.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 11.x framework)  
**Primary Dependencies**: Laravel Breeze (Blade templating), Tailwind CSS 3.x, Alpine.js 3.x  
**Storage**: MySQL/PostgreSQL (Laravel Eloquent ORM with migrations)  
**Testing**: Manual testing and validation (MVP phase - automated testing deferred)  
**Target Platform**: Web application (responsive design for desktop and mobile)  
**Project Type**: Laravel web application with Blade views (server-side rendered with Alpine.js reactivity)  
**Performance Goals**: Client-side validation feedback within 100ms; server-side validation within 200ms  
**Constraints**: Must handle concurrent booking attempts without double-bookings; must work with existing 15-minute lock mechanism  
**Scale/Scope**: 3-10 courts, 50-200 daily bookings, 100-500 active users

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### I. Code Clarity & Maintainability ✅

- **Self-documenting code**: Service methods will have clear names (`calculateMultiHourSlotOccupancy`, `getAvailableDurationsForSlot`), Blade template sections will be well-commented
- **Single Responsibility**: Extend AvailabilityService for slot calculations; BookingController remains focused on request handling; Blade views handle presentation only
- **Consistent code style**: Follows PSR-12 standards, existing Laravel conventions (service injection, Eloquent queries, Blade directives)
- **Avoid premature optimization**: Use straightforward slot availability calculations first; optimize only if performance issues arise
- **No dead code**: Remove any unused slot filtering logic from existing implementation

**Status**: ✅ PASS - Feature extends existing services cleanly without architectural changes

### II. Consistent UX Patterns ✅

- **Interaction patterns**: Follows existing booking form pattern (date → duration → time slot selection), consistent with Feature 001 and Feature 002
- **Visual hierarchy**: Uses existing Badge and Card components from Feature 002 design system; maintains consistent slot display styling
- **Feedback & messaging**: Provides immediate visual feedback (disabled buttons, tooltips) following existing error/success message patterns; loading states use existing spinner component
- **Accessibility fundamentals**: Form labels already present; disabled slots will have proper ARIA attributes and clear visual distinction
- **Mobile responsiveness**: Slot grid already responsive (grid-cols-4 sm:grid-cols-6); disabled state styling will maintain mobile usability

**Status**: ✅ PASS - Feature enhances existing UX without introducing new patterns

### Violations

None - Feature aligns with all constitution principles.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Http/Controllers/
│   └── BookingController.php         # Enhanced validation error messages
├── Models/
│   └── Booking.php                    # Existing model (no changes required)
├── Services/
│   ├── AvailabilityService.php        # NEW METHODS: getAvailableDurationsForSlot(), calculateMultiHourOccupancy()
│   └── BookingLockService.php         # Existing lock service (no changes required)
└── View/Components/
    └── badge.blade.php                 # Existing component (no changes required)

resources/views/
├── courts/
│   ├── index.blade.php                 # UPDATE: Show all slots (available/booked/locked) with tooltips
│   └── show.blade.php                  # UPDATE: Dynamic duration dropdown, disabled slot states, Alpine.js validation
└── components/
    └── badge.blade.php                 # Existing component from Feature 002

database/migrations/                    # No new migrations required
tests/                                  # Manual testing (MVP phase)
```

**Structure Decision**: Laravel web application structure; changes concentrated in AvailabilityService (business logic), BookingController (validation), and Blade templates (UI). No database schema changes required - feature uses existing Booking model with start_datetime and duration_hours attributes.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

No violations - table not applicable.

## Phase 0: Research & Discovery  COMPLETE

**Objective**: Resolve all technical unknowns before design phase

**Deliverables**:
- [research.md](research.md) - All NEEDS CLARIFICATION items resolved

**Key Decisions**:
1. Multi-hour slot occupancy: Calculate consecutive hourly slots from start_datetime + duration_hours
2. Dynamic duration validation: Check consecutive slot availability, show only valid durations in dropdown
3. Disabled slot display: Show all slots with distinct Badge component styling (success/secondary/warning variants)
4. Client-side validation: Extend existing Alpine.js reactive data with dynamic duration fetching
5. Error messages: Specific, actionable messages for each failure scenario (booked/locked/conflict/hours)
6. Tooltips: HTML title attribute with booking time range or lock status

**Status**:  All research complete - ready for design phase

---

## Phase 1: Design & Contracts  COMPLETE

**Objective**: Define data model, API contracts, and quick start guide

**Deliverables**:
- [data-model.md](data-model.md) - Entity definitions and service methods
- [contracts/api.md](contracts/api.md) - New/modified endpoints and service contracts
- [quickstart.md](quickstart.md) - Implementation guide with code examples
- Agent context updated (GitHub Copilot instructions)

**Key Artifacts**:
- **Data Model**: Booking (existing), Court (existing), TimeSlot (calculated, not stored)
- **New API Endpoint**: GET /api/courts/{court}/availability/durations - Dynamic duration validation
- **Modified Endpoint**: POST /bookings - Enhanced error messages (5 distinct scenarios)
- **Service Methods**: 
  - calculateOccupiedSlots() - Multi-hour slot calculation
  - getAvailableDurationsForSlot() - Dynamic duration validation
  - getAllSlotsWithStatus() - Complete slot status with metadata
- **Frontend**: Alpine.js reactive data structure with AJAX duration fetching

**Constitution Re-Check**:  PASS - All design decisions align with constitution principles

**Status**:  Design complete - ready for task breakdown

---

## Phase 2: Task Breakdown (Next Step)

**Objective**: Generate detailed implementation tasks from design

**Command**: Run \/speckit.tasks\ to generate [tasks.md](tasks.md)

**Expected Output**:
- Detailed task list with priorities (P1, P2, P3)
- Task dependencies and sequencing
- Acceptance criteria for each task
- Estimated complexity

**Status**:  PENDING - Run \/speckit.tasks\ command to proceed

---

## Implementation Summary

**Phase 0 Outcome**: All technical unknowns resolved. Implementation approach validated against existing Laravel/Blade/Alpine.js/Tailwind architecture. No new dependencies required.

**Phase 1 Outcome**: Complete design specification with:
- 3 new AvailabilityService methods
- 1 new API endpoint (duration validation)
- Enhanced BookingController error handling (5 distinct error messages)
- Updated courts/show.blade.php with Alpine.js reactive validation
- Updated courts/index.blade.php to show all slots with status badges
- No database schema changes

**Ready for Implementation**: All design artifacts complete. Feature aligns with constitution. Ready to proceed to task breakdown (Phase 2) using \/speckit.tasks\ command.

**Estimated Complexity**: Medium
- Service layer changes: 3 new methods, 1 modified method (Low complexity)
- Controller changes: 1 new API endpoint, enhanced validation logic (Low complexity)
- Frontend changes: Alpine.js reactive data, dynamic AJAX calls, slot display logic (Medium complexity)
- No database migrations or architectural changes (reduces risk)

**Implementation Time Estimate**: 6-8 hours for experienced Laravel developer
- Phase 0 (Service layer): 2 hours
- Phase 1 (API endpoint): 1 hour
- Phase 2 (Frontend Alpine.js): 2 hours
- Phase 3 (Slot display UI): 2 hours
- Testing and refinement: 1-2 hours
