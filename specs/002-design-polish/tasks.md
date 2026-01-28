# Tasks: UI/UX Design Polish

**Branch**: `002-design-polish` | **Date**: 2026-01-28  
**Input**: Design documents from `/specs/002-design-polish/`  
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/components.md, quickstart.md

**Tests**: Not included - manual visual testing per MVP constraints

**Organization**: Tasks grouped by user story to enable independent implementation and testing of each design improvement.

## Format: `- [ ] [ID] [P?] [Story?] Description with file path`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: User story label (US1, US2, US3, US4, US5)
- File paths use Laravel project structure

---

## Phase 1: Setup (Design System Foundation)

**Purpose**: Create reusable Blade components that form the design system foundation

**Status**: 7/7 tasks complete (100%) ✅

- [X] T001 [P] Create Button component in resources/views/components/button.blade.php with 4 variants (primary, secondary, danger, link) and 5 states (default, hover, active, disabled, loading)
- [X] T002 [P] Create Form Input component in resources/views/components/form-input.blade.php with label, validation, error handling, and disabled state
- [X] T003 [P] Create Form Label component in resources/views/components/form-label.blade.php with required indicator support
- [X] T004 [P] Create Form Error component in resources/views/components/form-error.blade.php with exclamation icon and red text
- [X] T005 [P] Create Card component in resources/views/components/card.blade.php with 3 variants (default, stat, table) and hoverable option
- [X] T006 [P] Create Badge component in resources/views/components/badge.blade.php with 7 status types (available, booked, locked, confirmed, cancelled, active, disabled)
- [X] T007 [P] Create Loading Spinner component in resources/views/components/loading-spinner.blade.php with 3 sizes and 3 colors

**Checkpoint**: Design system components ready - can now apply to pages ✅

---

## Phase 2: User Story 1 - Visual Consistency Across All Pages (Priority: P1) 🎯

**Goal**: Users interact with visually consistent interface where buttons, forms, and controls follow unified design system

**Status**: 12/12 tasks complete (100%) ✅

**Independent Test**: Navigate through all pages and verify consistent button styling, form inputs, and spacing rhythm

### Implementation for User Story 1

- [X] T008 [US1] Update courts index page in resources/views/courts/index.blade.php to use Button component for "View Details" links and "Book Now" actions
- [X] T009 [US1] Update court detail page in resources/views/courts/show.blade.php to use Button component for booking actions and back navigation
- [X] T010 [US1] Update payment page in resources/views/bookings/payment.blade.php to replace inline button styles with Button component (submit payment button)
- [X] T011 [US1] Update confirmation page in resources/views/bookings/confirmation.blade.php to use Button component for "Back to Courts" link
- [X] T012 [US1] Update admin dashboard in resources/views/admin/dashboard.blade.php to use Button component for all action buttons (Manage Courts, Manage Bookings, Add New Court)
- [X] T013 [US1] Update admin courts index in resources/views/admin/courts/index.blade.php to use Button component for disable/enable/delete actions
- [X] T014 [US1] Update admin courts create in resources/views/admin/courts/create.blade.php to use Button component for submit and cancel buttons
- [X] T015 [US1] Update admin bookings index in resources/views/admin/bookings/index.blade.php to use Button component for cancel action buttons
- [X] T016 [US1] Update login page in resources/views/auth/login.blade.php to use Button component for submit button and "Forgot password?" link
- [X] T017 [US1] Update register page in resources/views/auth/register.blade.php to use Button component for submit button and "Already registered?" link
- [X] T018 [US1] Audit all pages for spacing consistency - apply mb-4 (16px) to form fields, mb-6 (24px) to sections, mb-8 (32px) between major page sections
- [X] T019 [US1] Update page headers across all views to use text-2xl font-semibold text-gray-800 mb-8 for consistent visual hierarchy

**Checkpoint**: User Story 1 complete - Visual consistency established across all pages ✅

---

## Phase 3: User Story 2 - Interactive Button States (Priority: P1) 🎯

**Goal**: Users receive clear visual feedback through hover, active, disabled, and loading states

**Status**: 6/6 tasks complete (100%) ✅

**Independent Test**: Test all buttons for hover effects, loading spinners during async operations, and proper disabled states

### Implementation for User Story 2

- [X] T020 [P] [US2] Add loading state to payment submit button in resources/views/bookings/payment.blade.php using Alpine.js x-data and Button component loading prop
- [X] T021 [P] [US2] Add loading state to booking creation button in resources/views/courts/show.blade.php when user clicks "Book Now"
- [X] T022 [P] [US2] Add loading state to admin court creation form in resources/views/admin/courts/create.blade.php submit button
- [X] T023 [P] [US2] Add loading state to admin court delete action in resources/views/admin/courts/index.blade.php using form submission state
- [X] T024 [P] [US2] Add loading state to admin booking cancellation in resources/views/admin/bookings/index.blade.php
- [X] T025 [US2] Verify hover effects work on all buttons - test that transform scale-105 and shadow-md apply on hover without layout shifts

**Checkpoint**: User Story 2 complete - Interactive feedback implemented on all buttons ✅

---

## Phase 4: User Story 3 - Form Control Layout & Alignment (Priority: P2)

**Goal**: Users interact with properly aligned forms where labels, inputs, and errors are positioned consistently

**Status**: 8/8 tasks complete (100%) ✅

**Independent Test**: Complete registration, booking, and admin court creation forms verifying proper label alignment, error message positioning, and mobile touch targets

### Implementation for User Story 3

- [X] T026 [US3] Update login form in resources/views/auth/login.blade.php to use Form Input component for email and password fields with proper labels
- [X] T027 [US3] Update register form in resources/views/auth/register.blade.php to use Form Input component for all fields (name, email, phone, password, password_confirmation) with validation errors
- [X] T028 [US3] Update payment form in resources/views/bookings/payment.blade.php to use Form Input component for amount field with validation
- [X] T029 [US3] Update admin court create form in resources/views/admin/courts/create.blade.php to use Form Input components for name, description, photo_url, hourly_price, operating_hours fields
- [X] T030 [US3] Add Form Error component to display validation errors below each input field in all forms (login, register, payment, admin court create)
- [X] T031 [US3] Ensure all form inputs have min-height of 44px (py-2 = 8px + 8px = 16px + text height ≈ 44px) for mobile touch targets
- [X] T032 [US3] Update form layouts to use consistent spacing: mb-4 (16px) between form fields, mb-6 (24px) before submit buttons
- [X] T033 [US3] Test forms on mobile viewport (375px width) to verify labels remain readable, inputs don't overflow, and error messages display properly

**Checkpoint**: User Story 3 complete - Form layouts consistent and mobile-friendly

---

## Phase 5: User Story 4 - Admin Panel Visual Hierarchy (Priority: P2)

**Goal**: Admin users navigate clear visual hierarchy with organized statistics, tables, and action buttons

**Status**: 9/9 tasks complete (100%) ✅

**Independent Test**: Login as admin, verify stat cards use colored accents, tables have clear headers, and action buttons are visually distinct

### Implementation for User Story 4

- [X] T034 [US4] Update admin dashboard statistics cards in resources/views/admin/dashboard.blade.php to use Card component with variant="stat" and colored accents (blue, green, orange, red)
- [X] T035 [US4] Update admin dashboard recent bookings section to use Card component with variant="table" wrapping the table element
- [X] T036 [US4] Update admin courts table in resources/views/admin/courts/index.blade.php to use Card component with variant="table" and proper table headers (bg-gray-50, border-b, px-6 py-3)
- [X] T037 [US4] Update admin bookings table in resources/views/admin/bookings/index.blade.php to use Card component with variant="table" and consistent table styling
- [X] T038 [US4] Style table headers across all admin tables with: bg-gray-50, text-xs, font-semibold, text-gray-700, uppercase, text-left, px-6 py-3, border-b border-gray-200
- [X] T039 [US4] Style table rows across all admin tables with: px-6 py-4, text-sm, text-gray-800, border-b border-gray-100, last row border-b-0, hover:bg-gray-50 transition-colors
- [X] T040 [US4] Position action buttons (edit, delete, cancel) in table rows using text-right alignment and gap-2 between buttons
- [X] T041 [US4] Update page headers in all admin pages (dashboard, courts, bookings) to use text-2xl font-semibold text-gray-800 mb-8 for clear section separation
- [X] T042 [US4] Add colored left border (border-l-4) to stat cards matching their semantic meaning: blue=courts, green=bookings, orange=locked, red=revenue

**Checkpoint**: User Story 4 complete - Admin panel has clear visual hierarchy

---

## Phase 6: User Story 5 - Court Card Visual Appeal (Priority: P3)

**Goal**: Users browse visually appealing court cards with proper images, prominent pricing, and color-coded availability

**Status**: 7/7 tasks complete (100%) ✅

**Independent Test**: View courts listing and verify images fill cards without distortion, pricing is prominent, and availability badges are color-coded

### Implementation for User Story 5

- [X] T043 [US5] Wrap each court card in resources/views/courts/index.blade.php with Card component using hoverable="true" prop
- [X] T044 [US5] Ensure court images use object-cover class and h-48 (192px) height to fill card without distortion in resources/views/courts/index.blade.php
- [X] T045 [US5] Update pricing display in court cards to use text-2xl font-bold text-green-600 for price, text-sm text-gray-500 for "per hour"
- [X] T046 [US5] Replace availability slot badges with Badge component using status="available", status="booked", status="locked" in resources/views/courts/index.blade.php
- [X] T047 [US5] Add hover effect to court cards using Card component's hoverable prop - should scale-102 and increase shadow on hover
- [X] T048 [US5] Update court card layout to use consistent internal spacing: p-4 for card padding, mb-2 for title spacing, mb-3 for description spacing, mb-4 for pricing spacing
- [X] T049 [US5] Add rounded-full class to all availability badges for pill-shaped appearance with px-3 py-1 padding and text-xs font-medium

**Checkpoint**: User Story 5 complete - Court cards are visually appealing and engaging

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Final improvements and validation across all design improvements

**Status**: 6/6 tasks complete (100%) ✅

- [X] T050 [P] Update Tailwind config in tailwind.config.js to extend colors if needed for custom shades (verify blue-500, green-500, red-500, orange-500, gray-50 through gray-800 are available)
- [X] T051 [P] Run `npm run build` to rebuild Tailwind CSS with all new utility classes used in components
- [X] T052 Audit all Badge component usage across pages to ensure consistent status mapping: available=green, booked=gray, locked=orange, confirmed=blue, cancelled=red, active=green, disabled=gray
- [X] T053 Test responsive breakpoints on all pages at 320px, 375px, 768px, 1024px, 1280px viewports to verify no layout breaking or horizontal scroll
- [X] T054 Verify all interactive elements (buttons, inputs, links) have visible focus rings for keyboard navigation accessibility
- [X] T055 Run visual comparison test: compare before/after screenshots of courts listing, admin dashboard, login page, and booking flow to validate design improvements

**Checkpoint**: Polish complete - Design system fully implemented and validated

---

## Dependencies

### Between User Stories

- **US1 (Visual Consistency)**: Depends on Phase 1 (Setup) - requires all components created first
- **US2 (Interactive States)**: Depends on US1 complete - builds on button implementation
- **US3 (Form Layout)**: Depends on Phase 1 (Setup) - requires Form Input, Form Label, Form Error components
- **US4 (Admin Hierarchy)**: Depends on Phase 1 (Setup) - requires Card and Badge components
- **US5 (Court Cards)**: Depends on Phase 1 (Setup) - requires Card and Badge components

All user stories (US1-US5) are INDEPENDENT after Phase 1 completes - they can be implemented in parallel by different developers.

### Within Each User Story

**Phase 1 (Setup)**:
- All 7 component tasks (T001-T007) can run in parallel - each creates separate file with no dependencies

**User Story 1**:
- T008-T017 (Page updates) can run in parallel after Phase 1 - each updates different file
- T018-T019 (Spacing audits) depend on T008-T017 complete to verify consistency

**User Story 2**:
- T020-T024 (Loading states) can run in parallel - each updates different page
- T025 (Hover verification) depends on T020-T024 complete

**User Story 3**:
- T026-T029 (Form updates) can run in parallel - each updates different form
- T030 (Error components) depends on T026-T029 complete
- T031-T033 (Touch targets & mobile testing) sequential after T030

**User Story 4**:
- T034-T037 (Card replacements) can run in parallel - different pages
- T038-T039 (Table styling) sequential after T034-T037
- T040-T042 (Final touches) sequential after T038-T039

**User Story 5**:
- T043-T046 (Court card updates) can run in parallel within court listing page
- T047-T049 (Polish) sequential after T043-T046

**Phase 7 (Polish)**:
- T050-T051 (Tailwind rebuild) can run in parallel
- T052-T055 (Testing) sequential after all user stories complete

### Parallel Opportunities

**Phase 1 (All parallel)**:
```bash
T001 (Button), T002 (Form Input), T003 (Form Label), T004 (Form Error), 
T005 (Card), T006 (Badge), T007 (Spinner) - all can run simultaneously
```

**After Phase 1 completes, 5 developers can work in parallel**:
```bash
Developer 1: US1 (Visual Consistency) - T008-T019
Developer 2: US2 (Interactive States) - T020-T025
Developer 3: US3 (Form Layout) - T026-T033
Developer 4: US4 (Admin Hierarchy) - T034-T042
Developer 5: US5 (Court Cards) - T043-T049
```

---

## Parallel Example: Phase 1 (Setup)

```bash
# All component creation tasks run in parallel:
T001: Create Button component
T002: Create Form Input component
T003: Create Form Label component
T004: Create Form Error component
T005: Create Card component
T006: Create Badge component
T007: Create Loading Spinner component

# Wait for all to complete before Phase 2
```

---

## Parallel Example: User Story 1 (Visual Consistency)

```bash
# After Phase 1 complete, page updates run in parallel:
T008: Update courts index
T009: Update court detail
T010: Update payment page
T011: Update confirmation page
T012: Update admin dashboard
T013: Update admin courts index
T014: Update admin courts create
T015: Update admin bookings index
T016: Update login page
T017: Update register page

# Then sequential spacing audits:
T018: Spacing consistency audit
T019: Header consistency audit
```

---

## Implementation Strategy

### MVP First (User Story 1 + User Story 2 Only)

1. **Complete Phase 1**: Setup (T001-T007) - ~2 hours
   - Creates all 7 reusable components
   - Foundation for all subsequent work
   
2. **Complete User Story 1**: Visual Consistency (T008-T019) - ~2 hours
   - Applies button component to all 10 pages
   - Establishes consistent spacing and typography
   - Delivers immediate visible improvement
   
3. **Complete User Story 2**: Interactive States (T020-T025) - ~1.5 hours
   - Adds loading spinners to async operations
   - Verifies hover effects work correctly
   - Prevents double-submission issues

**MVP Deliverable** (5.5 hours): All pages have consistent buttons with proper interactive states. This alone transforms the application's visual quality and usability.

### Full Feature (All User Stories)

4. **Complete User Story 3**: Form Layout (T026-T033) - ~2 hours
   - Improves form usability significantly
   - Critical for registration and booking flows
   
5. **Complete User Story 4**: Admin Hierarchy (T034-T042) - ~2 hours
   - Enhances admin workflow efficiency
   - Makes admin panel professional
   
6. **Complete User Story 5**: Court Cards (T043-T049) - ~1.5 hours
   - Final polish on court browsing experience
   - Increases engagement with court listings

7. **Complete Phase 7**: Polish (T050-T055) - ~1 hour
   - Final validation and testing
   - Ensures no regressions

**Total Time Estimate**: 12-14 hours for complete design system implementation

### Testing Strategy

**Per User Story Testing** (as each completes):
1. Visual inspection: Does it match design system specs?
2. Interactive test: Do all states work (hover, active, disabled, loading)?
3. Responsive test: Works on mobile (375px) and desktop (1280px)?
4. Accessibility test: Keyboard navigation works, focus visible?

**Final Integration Testing** (Phase 7):
1. Cross-browser: Chrome, Firefox, Safari, Edge
2. Full user flows: Registration → Booking → Payment → Confirmation
3. Admin flows: Login → Dashboard → Create Court → Manage Bookings
4. Performance: Page load times remain fast (<2s)

---

## Task Count Summary

- **Phase 1 (Setup)**: 7 tasks
- **Phase 2 (US1 - Visual Consistency)**: 12 tasks
- **Phase 3 (US2 - Interactive States)**: 6 tasks
- **Phase 4 (US3 - Form Layout)**: 8 tasks
- **Phase 5 (US4 - Admin Hierarchy)**: 9 tasks
- **Phase 6 (US5 - Court Cards)**: 7 tasks
- **Phase 7 (Polish)**: 6 tasks

**Total**: 55 tasks

**Parallel Potential**: 
- Phase 1: 7 tasks can run in parallel
- After Phase 1: 5 user stories can be developed in parallel (39 tasks across 5 developers)
- With 5 developers: Complete in 2-3 days
- With 1 developer: Complete in 12-14 hours (1.5-2 days)
