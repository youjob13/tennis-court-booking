# Feature Specification: Tennis Court Booking System

**Feature Branch**: `001-tennis-court-booking`  
**Created**: 2026-01-27  
**Status**: Draft  
**Input**: User description: "Build an application that can help users book tennis courts. Tennis courts can be booked for a time period 1hour-2hour-3hour etc until 8hours. When booking in the middle of the process, selected for booking tennis court must be disabled for other users until payment will be made. All tennis courts must be available in the main page of the app, like a list with card. Card with small description, booked and available time slots, price and photo. Also we need to add ability to register and login in the app. So we need to create appropriated auth page for this purpose."

## Clarifications

### Session 2026-01-27

- Q: When exactly should the system lock a time slot to prevent other users from booking it? → A: Lock when user clicks "Proceed to Payment" or "Book Now" button after selecting duration
- Q: When a payment fails or is cancelled, how quickly should the system release the locked time slot? → A: After 30 seconds delay to allow user to retry payment
- Q: What should happen when two users click "Proceed to Payment" for the same time slot at nearly the same moment (within milliseconds)? → A: First user's request locks the slot; second user gets immediate error message and must select different slot
- Q: Can unauthenticated (guest) users book tennis courts, or must users be logged in to make a booking? → A: Require users to be logged in before they can book (authentication mandatory for booking)
- Q: How should available time slots be displayed on the court cards? → A: Hourly blocks (8 AM, 9 AM, 10 AM...)

**Additional scope identified**: Admin functionality - cancel bookings, add/remove courts, disable court booking
- Q: How should administrators access the admin functions (cancel bookings, manage courts)? → A: Separate admin dashboard accessible only to admin accounts at dedicated URL (e.g., /admin)
- Q: When an admin cancels a user's booking, what should happen to the payment? → A: Admin can only cancel bookings if payment is not made (locked state); paid bookings cannot be cancelled by admin
- Q: When an admin disables a court, what happens to its existing future bookings? → A: Existing future bookings remain valid; only new bookings are prevented for disabled court
- Q: What's the difference between "removing" a court and "disabling" it? → A: Remove = permanent deletion (cannot be undone), Disable = temporary deactivation (can be re-enabled later)
- Q: How are admin users created and distinguished from regular users? → A: Regular users with "admin" role flag in database (set manually by developer/super-admin)
- Q: Can unauthenticated (guest) users book tennis courts, or must users be logged in to make a booking? → A: Require users to be logged in before they can book (authentication mandatory for booking)

### Session 2026-01-27 (Edge Case Validation)

- Q: How does the system prevent users from booking beyond the facility's operating hours (e.g., 8-hour booking starting at 6 PM when facility closes at 10 PM)? → A: Prevent bookings that extend past operating hours (validate start_time + duration <= operating_hours.end)
- Q: What happens when the same user tries to book multiple courts simultaneously? → A: Allow unlimited simultaneous locks per user (each with own 10-minute expiration)
- Q: Can users book a court that starts in 5 minutes, or is there a minimum advance notice requirement? → A: No minimum notice required (validates only after:now for maximum flexibility)

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Browse Available Courts (Priority: P1)

Users can view all tennis courts with essential information to make an informed booking decision. Each court is displayed as a card showing its description, photo, pricing, and real-time availability for different time slots throughout the day.

**Why this priority**: This is the foundation of the entire booking system. Without the ability to view courts and their availability, users cannot proceed with any booking. This represents the minimum viable functionality that delivers immediate value - users can see what's available.

**Independent Test**: Can be fully tested by loading the main page and verifying that court cards display with all required information (description, photo, price, availability) without needing any booking or authentication functionality.

**Acceptance Scenarios**:

1. **Given** a user visits the main page, **When** the page loads, **Then** all tennis courts are displayed as individual cards in a list format
2. **Given** a court card is displayed, **When** user views the card, **Then** it shows court description, photo, hourly price, and available time slots for the current day displayed as hourly blocks (8 AM, 9 AM, 10 AM, etc.)
3. **Given** multiple courts exist, **When** user scrolls the list, **Then** all courts are accessible and information loads without delays
4. **Given** a time slot is already booked, **When** user views that court's card, **Then** the booked time slot is clearly marked as unavailable
5. **Given** a time slot is currently being booked by another user, **When** user views that court's card, **Then** the slot is marked as temporarily unavailable

---

### User Story 2 - Book Court with Payment Lock (Priority: P2)

Users can select a tennis court, choose a booking duration (1-8 hours), and proceed through a payment process. During the payment process, the selected court and time slot are temporarily locked to prevent double-booking, ensuring a smooth transaction experience.

**Why this priority**: This is the core transactional functionality that generates business value. Once users can browse courts (P1), enabling them to actually book creates a complete booking flow. The payment lock mechanism is critical for preventing booking conflicts.

**Independent Test**: Can be tested by selecting a court from the main page, choosing a time duration, and proceeding to payment. Verification includes checking that another user cannot book the same slot during the payment process, and that the lock releases if payment is abandoned.

**Acceptance Scenarios**:

1. **Given** a user selects an available court, **When** they choose a booking duration between 1-8 hours, **Then** the system displays available start times for that duration
2. **Given** an unauthenticated user attempts to book a court, **When** they click "Proceed to Payment" or "Book Now", **Then** the system redirects them to the login page and preserves their court selection
3. **Given** a logged-in user selects a specific time slot and clicks "Proceed to Payment" or "Book Now", **When** the button is clicked, **Then** that time slot is immediately locked and marked as unavailable to other users
4. **Given** a locked time slot, **When** another user views the court, **Then** that slot shows as temporarily unavailable
5. **Given** a user is in the payment process, **When** they complete payment successfully, **Then** the booking is confirmed and the slot remains permanently booked
6. **Given** a user is in the payment process, **When** they abandon the payment or the process times out (standard 10-minute session timeout), **Then** the lock is released and the slot becomes available again
7. **Given** a user selects a 3-hour booking, **When** they choose 2:00 PM as start time, **Then** the system blocks 2:00 PM, 3:00 PM, and 4:00 PM slots for that court
8. **Given** a user attempts to book overlapping hours, **When** one hour in the range is unavailable, **Then** the system prevents the booking and suggests alternative times
9. **Given** two users click "Proceed to Payment" for the same slot simultaneously, **When** the first user's request is processed, **Then** that user proceeds to payment while the second user receives an immediate error message indicating the slot is no longer available

---

### User Story 3 - User Authentication (Priority: P3)

Users can create an account and log in to the application to manage their bookings. The authentication system allows users to register with basic information, log in securely, and maintain their session across multiple interactions.

**Why this priority**: While important for tracking bookings and user management, authentication can be deferred after the core booking functionality works. An MVP could theoretically allow guest bookings initially, making this the lowest priority of the three core features.

**Independent Test**: Can be tested by accessing the registration page, creating a new account, logging out, and logging back in. Verification includes ensuring proper session management and password security without requiring the booking system to be functional.

**Acceptance Scenarios**:

1. **Given** a new user, **When** they access the registration page, **Then** they can create an account with email, password, and basic profile information (name, phone number)
2. **Given** a registered user, **When** they enter correct credentials on the login page, **Then** they are authenticated and redirected to the main page
3. **Given** a logged-in user, **When** they navigate through the application, **Then** their session persists across pages
4. **Given** an invalid login attempt, **When** user enters incorrect credentials, **Then** an appropriate error message is displayed without exposing security details
5. **Given** a logged-in user, **When** they log out, **Then** their session is terminated and they cannot access booking functions without re-authenticating
6. **Given** a user registration attempt with an already-used email, **When** they try to register, **Then** the system prevents duplicate registration and provides clear feedback

---

### User Story 4 - Administrative Court & Booking Management (Priority: P4)

Administrators can manage tennis courts and bookings through a dedicated admin dashboard. Admins can add new courts, remove courts permanently, temporarily disable courts, and cancel locked (unpaid) bookings to maintain facility operations.

**Why this priority**: Admin functionality is essential for operational management but is lower priority than core user-facing features. The system can function initially with a fixed set of courts, making this deferrable until after the booking system works.

**Independent Test**: Can be tested by logging in as an admin user, accessing the /admin dashboard, and performing court management operations (add, disable, remove) and cancelling locked bookings without requiring the regular user booking flow to be complete.

**Acceptance Scenarios**:

1. **Given** an admin user logs in, **When** they navigate to the admin dashboard URL, **Then** they see administrative interface with court management and booking management sections
2. **Given** an admin is on the admin dashboard, **When** they add a new court with name, description, photo, price, and operating hours, **Then** the court immediately appears in the main court listing for regular users
3. **Given** an admin views the court list, **When** they select a court and choose "Disable", **Then** the court remains visible but prevents new bookings while existing bookings remain valid
4. **Given** a disabled court exists, **When** admin selects "Enable", **Then** the court becomes available for new bookings again
5. **Given** an admin views a court with no future bookings, **When** they select "Remove", **Then** the court is permanently deleted from the system
6. **Given** an admin views the bookings list, **When** they filter for locked (unpaid) bookings, **Then** they see all time slots currently locked but not yet paid
7. **Given** an admin selects a locked booking, **When** they click "Cancel", **Then** the lock is released and the time slot becomes available immediately
8. **Given** an admin views a paid booking, **When** they attempt to cancel it, **Then** the system prevents cancellation and shows a message that paid bookings cannot be cancelled
9. **Given** a regular (non-admin) user, **When** they attempt to access the admin dashboard URL, **Then** they are denied access and redirected to the main page

---

### Edge Cases

- **Court availability boundary**: What happens when a user tries to book a slot that starts available but extends into an already-booked time period? → Resolved: FR-008 validates all consecutive hours available before allowing selection
- **Payment lock expiration**: How does the system handle cases where a user's payment process is interrupted (browser crash, network failure) and the lock needs to be released? → Resolved: Scheduled job releases expired locks after 10 minutes (FR-010)
- **Same-user multiple locks**: What happens when the same user tries to book multiple courts simultaneously? → Resolved: Users can hold multiple active locks simultaneously, each with independent 10-minute expiration
- **Maximum booking duration**: How does the system prevent users from booking beyond the facility's operating hours (e.g., 8-hour booking starting at 6 PM when facility closes at 10 PM)? → Resolved: FR-007a validates booking doesn't extend past operating hours
- **Concurrent booking attempts**: When two users select the same slot at nearly the same time, the first request to reach the server locks the slot and the second user receives an immediate error message → Resolved: FR-011a uses atomic operations
- **Date boundaries**: How are bookings handled across midnight or when users want to book for future dates? → Deferred: MVP shows Resolved: No minimum notice required; validates only start_datetime > nowAssumptions section
- **Minimum booking notice**: Can users book a court that starts in 5 minutes, or is there a minimum advance notice requirement? → Pending clarification

## Requirements *(mandatory)*

### Functional Requirements

**Court Display & Information**
- **FR-001**: System MUST display all tennis courts on the main page as a list of cards
- **FR-002**: Each court card MUST include a photo, description text, and hourly price
- **FR-003**: Each court card MUST show real-time availability status for all time slots displayed as hourly blocks (e.g., 8 AM, 9 AM, 10 AM)
- **FR-004**: System MUST visually distinguish between available, booked, and temporarily locked time slots
- **FR-005**: System MUST refresh availability status automatically when other users make bookings

**Booking & Time Slot Management**
- **FR-006**: System MUST require users to be authenticated (logged in) before allowing them to proceed with booking
- **FR-007**: System MUST allow users to select booking durations in 1-hour increments from 1 hour up to 8 hours
- **FR-007a**: System MUST prevent bookings that extend past the court's operating hours by validating start_time + duration <= operating_hours.end
- **FR-008**: System MUST validate that all consecutive hours in a booking duration are available before allowing selection
- **FR-009**: System MUST immediately lock a time slot when a user clicks "Proceed to Payment" or "Book Now" button after selecting court, time, and duration
- **FR-010**: System MUST release locked time slots after 10 minutes if payment is not completed
- **FR-011**: System MUST prevent double-booking by enforcing that locked or booked slots cannot be selected by other users
- **FR-011a**: System MUST use atomic operations to ensure only one user can successfully lock a time slot when concurrent requests occur
- **FR-012**: System MUST mark permanently booked slots as unavailable for all users
- **FR-013**: System MUST calculate total price based on hourly rate multiplied by booking duration

**Payment Process**
- **FR-014**: System MUST provide a payment interface that accepts standard payment methods (credit card, debit card)
- **FR-015**: System MUST confirm booking only after successful payment completion
- **FR-016**: System MUST notify users of successful booking with confirmation details
- **FR-017**: System MUST release slot locks 30 seconds after payment fails or is cancelled to allow user retry opportunity

**User Authentication**
- **FR-018**: System MUST provide a registration page where new users can create accounts
- **FR-019**: User registration MUST collect email address, password, full name, and phone number
- **FR-020**: System MUST validate email format and password strength during registration
- **FR-021**: System MUST prevent duplicate account registration with the same email address
- **FR-022**: System MUST provide a login page where registered users can authenticate
- **FR-023**: System MUST maintain user sessions after successful login
- **FR-024**: System MUST allow users to log out and terminate their session
- **FR-025**: System MUST store passwords securely using industry-standard hashing
- **FR-025a**: System MUST distinguish admin users from regular users via role flag in user account

**Administrative Functions**
- **FR-026**: System MUST provide a dedicated admin dashboard at a protected URL accessible only to users with admin role
- **FR-027**: System MUST allow admins to add new courts with name, description, photo URL, hourly price, and operating hours
- **FR-028**: System MUST allow admins to temporarily disable courts, preventing new bookings while preserving existing bookings
- **FR-029**: System MUST allow admins to re-enable previously disabled courts
- **FR-030**: System MUST allow admins to permanently remove courts that have no future bookings
- **FR-031**: System MUST prevent admins from removing courts that have existing future bookings
- **FR-032**: System MUST allow admins to view all locked (unpaid) bookings across all courts
- **FR-033**: System MUST allow admins to cancel locked bookings, immediately releasing the time slot
- **FR-034**: System MUST prevent admins from cancelling paid/confirmed bookings
- **FR-035**: System MUST deny access to admin dashboard for users without admin role

**Data Management**
- **FR-036**: System MUST persist all booking data including court, time slot, duration, user, and payment status
- **FR-037**: System MUST track booking lock status and expiration times
- **FR-038**: System MUST maintain user account data including credentials, profile information, and role (admin/regular)
- **FR-039**: System MUST track court status (active/disabled) and prevent modifications to courts with active bookings

### Key Entities

- **Court**: Represents a tennis court with attributes including unique identifier, name/description, photo URL, hourly price rate, operating hours, and status (active/disabled)
- **Booking**: Represents a court reservation with attributes including court reference, user reference, start date/time, duration in hours, total price, booking status (locked/confirmed), and payment confirmation
- **User**: Represents a registered user with attributes including email (unique identifier), hashed password, full name, phone number, registration date, and role (admin or regular user)
- **TimeSlot**: Represents a bookable time period for a specific court, with attributes including court reference, date, start time, status (available/locked/booked), and lock expiration timestamp if applicable

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Users can view all available courts with complete information within 2 seconds of loading the main page
- **SC-002**: Users can complete the entire booking process from court selection to payment confirmation in under 3 minutes
- **SC-003**: The system prevents double-booking in 100% of concurrent booking attempts through effective slot locking
- **SC-004**: Users receive immediate visual feedback (within 500ms) when selecting time slots or interacting with court cards
- **SC-005**: 95% of users successfully complete their first booking without needing support or clarification
- **SC-006**: New users can register and log in successfully in under 1 minute
- **SC-007**: The application displays correctly and remains fully functional on mobile devices (smartphones and tablets)
- **SC-008**: Locked time slots are released within 30 seconds after payment failure or cancellation, or after 10-minute timeout for abandoned sessions, to balance retry opportunities with availability

## Assumptions

- Facility operates on a fixed schedule (e.g., 8 AM to 10 PM daily) - specific hours can be configured per court by admin
- All prices are in a single currency and include applicable taxes
- Payment processing is handled by a third-party payment gateway that provides a standard integration interface
- Email notifications for booking confirmations are deferred to post-MVP
- Court availability is shown for the current date by default; future date booking can be added in later iterations
- Admin users are created manually (role flag set directly in database by developer/super-admin)
- Users must be logged in to complete bookings (guest checkout is not supported in MVP)

## Constraints & Dependencies

- The application must be mobile-responsive to accommodate users booking on smartphones at the tennis facility
- Payment processing depends on integration with a payment gateway API
- Session management and authentication must comply with basic security best practices (HTTPS, secure cookies, password hashing)
- The system must handle at least 50 concurrent users during peak booking times without performance degradation

## Out of Scope

The following features are explicitly excluded from this MVP and may be considered for future iterations:

- Court availability calendar view or date picker for future bookings
- User profile management (editing profile information, viewing booking history)
- Email or SMS notifications for booking confirmations and reminders
- User-initiated cancellation and refund processing (only admin-initiated cancellation of locked bookings is supported)
- Multi-facility support (single facility assumed)
- Membership or loyalty programs
- Special pricing (discounts, packages, off-peak rates)
- Court-specific amenities or equipment rental
- Weather-based cancellation policies
- Integration with external calendar systems
