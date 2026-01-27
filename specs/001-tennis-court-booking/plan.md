# Implementation Plan: Tennis Court Booking System

**Branch**: `001-tennis-court-booking` | **Date**: 2026-01-27 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/001-tennis-court-booking/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

Build a tennis court booking system where users can view available courts, book time slots (1-8 hours), and complete payments. The system implements a booking lock mechanism to prevent double-booking: when a user proceeds to payment, the selected time slot is immediately locked for 10 minutes. Locks are released after payment failure (30 seconds delay) or timeout. Administrators can manage courts (add/disable/remove) and cancel locked (unpaid) bookings through a dedicated admin dashboard.

Technical approach: Laravel PHP framework with PostgreSQL database, containerized with Docker for consistent development environment. Authentication uses Laravel's built-in system with role-based access control (admin/user). Payment integration deferred to research phase for payment gateway selection.

## Technical Context

**Language/Version**: PHP 8.2+ with Laravel 11.x framework  
**Primary Dependencies**: Laravel 11.x, Laravel Breeze (authentication scaffolding), Blade templates (UI), Tailwind CSS (styling), minimal additional packages  
**Storage**: PostgreSQL 15+ database with Laravel Eloquent ORM  
**Testing**: Manual testing and validation (automated testing deferred per MVP constraints)  
**Target Platform**: Web application (browser-based), containerized with Docker Compose  
**Project Type**: web (backend + frontend integrated in Laravel)  
**Performance Goals**: <2 second page load for court listing, <500ms response for booking actions, handle 50 concurrent users  
**Constraints**: Mobile-responsive UI required, PSR-12 code standards mandatory, 10-minute booking lock timeout, atomic lock acquisition to prevent race conditions  
**Scale/Scope**: Single facility, ~10-20 courts, estimated 100-500 daily bookings, MVP scope with core features only

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

### I. Code Clarity & Maintainability

✅ **PASS** - Laravel framework enforces PSR-12 standards by default. Plan specifies:
- Use Laravel's Eloquent ORM with clear model names (User, Court, Booking, TimeSlot)
- Follow Laravel conventions for controllers, models, migrations
- Blade templates for views with semantic naming
- Self-documenting code through Laravel's expressive syntax
- No custom abstractions beyond Laravel's built-in patterns

### II. Consistent UX Patterns

✅ **PASS** - Design approach ensures consistency:
- Tailwind CSS for consistent styling across all pages
- Shared Blade layout template for navigation, headers, footers
- Consistent form patterns for login, registration, booking flows
- Mobile-responsive design required from start (Tailwind responsive utilities)
- Laravel's validation provides consistent error messaging
- Admin dashboard uses same UI components as main app for consistency

### MVP Development Constraints

✅ **COMPLIANT** - Plan adheres to MVP constraints:
- No automated testing (manual validation only)
- Performance optimization deferred (functional correctness prioritized)
- Edge cases documented but advanced error handling deferred
- Documentation through inline code clarity, not extensive external docs
- Simple architecture using Laravel defaults, no premature scalability concerns

**GATE STATUS**: ✅ **APPROVED** - All constitution principles satisfied. Proceed to Phase 0 research.

---

## Post-Design Constitution Re-evaluation

*After Phase 1 completion (research, data model, contracts, quickstart)*

### I. Code Clarity & Maintainability

✅ **CONFIRMED PASS** - Design decisions reinforce clarity:
- Three-table schema (users, courts, bookings) is simple and self-explanatory
- No time_slots table - availability calculated on-demand (less complexity)
- Laravel Eloquent models map directly to business entities
- BookingLockService encapsulates complex lock logic in single service
- Route organization separates user and admin concerns clearly
- Migrations provide self-documenting schema evolution

### II. Consistent UX Patterns

✅ **CONFIRMED PASS** - Design ensures consistency:
- All pages use shared `layouts/app.blade.php` template
- Form validation errors displayed consistently via Laravel's validation
- Court cards on main page follow identical structure
- Admin pages reuse same components as user pages (consistent navigation)
- Tailwind utility classes ensure visual consistency
- All success/error states follow same feedback pattern

### MVP Development Constraints

✅ **CONFIRMED COMPLIANT** - Implementation path respects constraints:
- Docker setup enables quick local development (no complex deployment)
- Dummy PaymentService allows testing without real payment integration
- Manual database seeding sufficient for MVP data
- No caching layer, queue workers, or optimization premature for MVP
- Edge cases identified but complex error recovery deferred

**FINAL GATE STATUS**: ✅ **APPROVED** - Design maintains constitution compliance. Ready for implementation via `/speckit.tasks`.

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
Root Level:
├── docker-compose.yml         # Docker orchestration (app, db, nginx containers)
├── .env.example              # Environment configuration template
├── Dockerfile                # Laravel app container definition
└── README.md                 # Setup and run instructions

Laravel Application (created via composer):
app/
├── Http/
│   ├── Controllers/
│   │   ├── CourtController.php        # Court listing, details
│   │   ├── BookingController.php      # Booking flow, payment
│   │   ├── Admin/
│   │   │   ├── CourtController.php    # Admin court management
│   │   │   └── BookingController.php  # Admin booking cancellation
│   │   └── Auth/                      # Laravel Breeze auth controllers
│   └── Middleware/
│       └── IsAdmin.php                # Admin role guard
├── Models/
│   ├── User.php                       # User with role attribute
│   ├── Court.php                      # Court with status (active/disabled)
│   ├── Booking.php                    # Booking with lock/confirmed status
│   └── TimeSlot.php                   # Time slot availability tracking
└── Services/
    ├── BookingLockService.php         # Atomic lock acquisition logic
    └── PaymentService.php             # Payment gateway integration

database/
├── migrations/
│   ├── 2026_01_27_000001_create_users_table.php
│   ├── 2026_01_27_000002_create_roles_table.php
│   ├── 2026_01_27_000003_create_courts_table.php
│   ├── 2026_01_27_000004_create_bookings_table.php
│   └── 2026_01_27_000005_create_time_slots_table.php
└── seeders/
    ├── RoleSeeder.php                 # Admin and User roles
    └── CourtSeeder.php                # Initial court data

resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php              # Main layout with navigation
│   ├── courts/
│   │   ├── index.blade.php            # Court listing (main page)
│   │   ├── show.blade.php             # Court details page
│   │   └── book.blade.php             # Booking form
│   ├── admin/
│   │   ├── dashboard.blade.php        # Admin dashboard
│   │   ├── courts/
│   │   │   ├── index.blade.php        # Manage courts
│   │   │   └── create.blade.php       # Add new court
│   │   └── bookings/
│   │       └── index.blade.php        # Locked bookings list
│   └── auth/                          # Laravel Breeze auth views
└── css/
    └── app.css                        # Tailwind CSS

routes/
├── web.php                            # Main routes (courts, bookings, auth)
└── admin.php                          # Admin routes (separated for clarity)

config/
├── database.php                       # PostgreSQL connection
└── services.php                       # Payment gateway config

public/
└── storage/                           # Court photos (symlink to storage/app/public)
```

**Structure Decision**: Web application using Laravel's standard MVC structure. Single Laravel project contains both user-facing and admin functionality, separated by route groups and middleware. No separate frontend/backend split needed - Laravel Blade provides server-side rendering with Tailwind CSS for styling. Docker containers separate concerns (app, database, web server) while keeping codebase unified.

## Complexity Tracking

> No constitution violations - table left empty per template instructions.

**Status**: All design decisions align with constitution principles. Laravel's conventions provide sufficient structure without additional complexity.
