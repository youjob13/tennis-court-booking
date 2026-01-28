# Route Contracts: Tennis Court Booking System

**Purpose**: Define all HTTP routes, request/response formats for Laravel application  
**Date**: 2026-01-27  
**Type**: Server-side rendered pages (Blade) + form submissions

## Route Organization

### Public Routes (No Authentication Required)
- Authentication pages (login, register)
- Main court listing page (browse only)

### Authenticated User Routes
- Court details and booking
- Booking confirmation
- Logout

### Admin Routes (Requires Admin Role)
- Admin dashboard
- Court management (CRUD)
- Locked booking management

---

## 1. Authentication Routes

### GET /register
**Purpose**: Display registration form  
**Middleware**: guest  
**Controller**: Laravel Breeze RegisteredUserController  
**View**: `auth.register`  
**Response**: HTML page with registration form

**Form Fields**:
- `name` (string, required)
- `email` (email, required, unique)
- `phone` (string, optional)
- `password` (string, required, min:8)
- `password_confirmation` (string, required)

---

### POST /register
**Purpose**: Create new user account  
**Middleware**: guest  
**Controller**: RegisteredUserController@store  
**Request Body**:
```php
[
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '555-0123',
    'password' => 'password123',
    'password_confirmation' => 'password123'
]
```

**Validation**:
- name: required, string, max:255
- email: required, email, unique:users
- phone: nullable, string, max:50
- password: required, string, min:8, confirmed

**Success Response**: Redirect to `/` (main page) with authenticated session  
**Error Response**: Redirect back with validation errors

---

### GET /login
**Purpose**: Display login form  
**Middleware**: guest  
**Controller**: AuthenticatedSessionController  
**View**: `auth.login`  
**Response**: HTML page with login form

**Form Fields**:
- `email` (email, required)
- `password` (string, required)
- `remember` (checkbox, optional)

---

### POST /login
**Purpose**: Authenticate user  
**Middleware**: guest  
**Controller**: AuthenticatedSessionController@store  
**Request Body**:
```php
[
    'email' => 'john@example.com',
    'password' => 'password123',
    'remember' => true
]
```

**Validation**:
- email: required, email
- password: required, string

**Success Response**: Redirect to intended page or `/` (main page)  
**Error Response**: Redirect back with "Invalid credentials" error

---

### POST /logout
**Purpose**: Log out current user  
**Middleware**: auth  
**Controller**: AuthenticatedSessionController@destroy  
**Success Response**: Redirect to `/` with session destroyed

---

## 2. Court Routes (User-Facing)

### GET /
**Purpose**: Display main page with all courts listing  
**Middleware**: none (public)  
**Controller**: CourtController@index  
**View**: `courts.index`  
**Query Parameters**: None

**Response Data**:
```php
[
    'courts' => [
        [
            'id' => 1,
            'name' => 'Center Court',
            'description' => 'Premium court with lighting',
            'photo_url' => 'https://...',
            'hourly_price' => 50.00,
            'status' => 'active',
            'available_slots_today' => ['08:00', '09:00', '10:00', ...], // Example times
            'booked_slots_today' => ['14:00', '15:00', ...],
            'locked_slots_today' => ['16:00']
        ],
        // ... more courts
    ]
]
```

**UI Requirements**:
- Display courts as cards in grid layout
- Show court photo, name, description, price
- Show available/booked/locked time slots visually distinguished
- Mobile responsive layout

---

### GET /courts/{id}
**Purpose**: Display court details with booking form  
**Middleware**: auth  
**Controller**: CourtController@show  
**View**: `courts.show`  
**Path Parameters**: `id` (court ID)

**Response Data**:
```php
[
    'court' => [
        'id' => 1,
        'name' => 'Center Court',
        'description' => 'Full description...',
        'photo_url' => 'https://...',
        'hourly_price' => 50.00,
        'operating_hours' => ['start' => '08:00', 'end' => '22:00'],
        'available_slots' => [
            '2026-01-27' => ['08:00', '09:00', '10:00', ...],
            '2026-01-28' => ['08:00', '09:00', ...]
        ]
    ]
]
```

**UI Requirements**:
- Court photo and full description
- Duration selector (1-8 hours)
- Time slot selector (hourly blocks)
- "Proceed to Payment" button
- Display total price calculation

---

## 3. Booking Routes

### POST /bookings
**Purpose**: Create booking (lock time slot) and redirect to payment  
**Middleware**: auth  
**Controller**: BookingController@store  
**Request Body**:
```php
[
    'court_id' => 1,
    'start_datetime' => '2026-01-27 14:00:00',
    'duration_hours' => 2
]
```

**Validation**:
- court_id: required, exists:courts,id
- start_datetime: required, date, after:now
- duration_hours: required, integer, between:1,8

**Business Logic**:
1. Check court is active
2. Check all hours in duration are available
3. Acquire database lock (SELECT FOR UPDATE)
4. Create booking with status='locked', lock_expires_at=now+10min
5. Calculate total_price (hourly_price × duration)
6. Redirect to payment page

**Success Response**: Redirect to `/bookings/{id}/payment`  
**Error Responses**:
- 409 Conflict: "Time slot no longer available"
- 422 Validation Error: Invalid input data

---

### GET /bookings/{id}/payment
**Purpose**: Display payment form  
**Middleware**: auth  
**Controller**: BookingController@showPayment  
**View**: `bookings.payment`  
**Path Parameters**: `id` (booking ID)

**Response Data**:
```php
[
    'booking' => [
        'id' => 1,
        'court_name' => 'Center Court',
        'start_datetime' => '2026-01-27 14:00:00',
        'duration_hours' => 2,
        'total_price' => 100.00,
        'lock_expires_at' => '2026-01-27 13:10:00'
    ]
]
```

**UI Requirements**:
- Show booking summary (court, time, duration, price)
- Payment form (credit card fields - dummy for MVP)
- Lock expiration countdown timer
- "Complete Payment" button

---

### POST /bookings/{id}/payment
**Purpose**: Process payment and confirm booking  
**Middleware**: auth  
**Controller**: BookingController@processPayment  
**Request Body**:
```php
[
    'card_number' => '4242424242424242',  // Dummy for MVP
    'expiry' => '12/25',
    'cvv' => '123'
]
```

**Business Logic**:
1. Verify booking is locked and belongs to current user
2. Check lock hasn't expired
3. Call PaymentService->charge()
4. On success: Update status='confirmed', store payment_reference, set lock_expires_at=NULL
5. On failure: Set unlocked_after=now+30sec

**Success Response**: Redirect to `/bookings/{id}/confirmation`  
**Error Responses**:
- 410 Gone: "Booking lock expired"
- 402 Payment Required: "Payment failed - {reason}"

---

### GET /bookings/{id}/confirmation
**Purpose**: Display booking confirmation  
**Middleware**: auth  
**Controller**: BookingController@showConfirmation  
**View**: `bookings.confirmation`  
**Path Parameters**: `id` (booking ID)

**Response Data**:
```php
[
    'booking' => [
        'id' => 1,
        'court_name' => 'Center Court',
        'start_datetime' => '2026-01-27 14:00:00',
        'duration_hours' => 2,
        'total_price' => 100.00,
        'payment_reference' => 'PAY-12345',
        'created_at' => '2026-01-27 13:00:00'
    ]
]
```

**UI Requirements**:
- Success message
- Booking details
- Payment confirmation
- "Back to Courts" button

---

## 4. Admin Routes

### GET /admin
**Purpose**: Admin dashboard home  
**Middleware**: auth, admin  
**Controller**: Admin\DashboardController@index  
**View**: `admin.dashboard`  

**Response Data**:
```php
[
    'stats' => [
        'total_courts' => 10,
        'active_courts' => 8,
        'locked_bookings_count' => 5,
        'confirmed_bookings_today' => 23
    ]
]
```

**UI Requirements**:
- Statistics cards
- Quick links to court management and booking management
- Consistent navigation with main app

---

### GET /admin/courts
**Purpose**: List all courts for management  
**Middleware**: auth, admin  
**Controller**: Admin\CourtController@index  
**View**: `admin.courts.index`  

**Response Data**:
```php
[
    'courts' => [
        [
            'id' => 1,
            'name' => 'Center Court',
            'status' => 'active',
            'hourly_price' => 50.00,
            'future_bookings_count' => 12
        ],
        // ... more courts
    ]
]
```

**UI Requirements**:
- Table view with courts
- "Add Court" button
- Actions: Edit, Disable/Enable, Remove (if no future bookings)
- Status badge (active/disabled)

---

### GET /admin/courts/create
**Purpose**: Display form to add new court  
**Middleware**: auth, admin  
**Controller**: Admin\CourtController@create  
**View**: `admin.courts.create`  

**Form Fields**:
- name (string, required)
- description (textarea, optional)
- photo_url (string, optional)
- hourly_price (decimal, required)
- operating_hours_start (time, required)
- operating_hours_end (time, required)

---

### POST /admin/courts
**Purpose**: Create new court  
**Middleware**: auth, admin  
**Controller**: Admin\CourtController@store  
**Request Body**:
```php
[
    'name' => 'Court 5',
    'description' => 'New court description',
    'photo_url' => 'https://...',
    'hourly_price' => 45.00,
    'operating_hours' => ['start' => '08:00', 'end' => '22:00']
]
```

**Validation**:
- name: required, string, max:255
- description: nullable, string
- photo_url: nullable, url
- hourly_price: required, numeric, min:0
- operating_hours: required, json

**Success Response**: Redirect to `/admin/courts` with success message  
**Error Response**: Redirect back with validation errors

---

### PATCH /admin/courts/{id}/disable
**Purpose**: Disable court (prevent new bookings)  
**Middleware**: auth, admin  
**Controller**: Admin\CourtController@disable  
**Path Parameters**: `id` (court ID)

**Business Logic**:
- Set court status='disabled'
- Existing bookings remain valid

**Success Response**: Redirect back with "Court disabled" message

---

### PATCH /admin/courts/{id}/enable
**Purpose**: Re-enable disabled court  
**Middleware**: auth, admin  
**Controller**: Admin\CourtController@enable  
**Path Parameters**: `id` (court ID)

**Business Logic**:
- Set court status='active'

**Success Response**: Redirect back with "Court enabled" message

---

### DELETE /admin/courts/{id}
**Purpose**: Permanently remove court  
**Middleware**: auth, admin  
**Controller**: Admin\CourtController@destroy  
**Path Parameters**: `id` (court ID)

**Business Logic**:
- Check court has no future bookings
- Delete court record

**Success Response**: Redirect to `/admin/courts` with "Court removed" message  
**Error Response**: 409 Conflict - "Cannot remove court with future bookings"

---

### GET /admin/bookings
**Purpose**: List locked (unpaid) bookings  
**Middleware**: auth, admin  
**Controller**: Admin\BookingController@index  
**View**: `admin.bookings.index`  

**Response Data**:
```php
[
    'locked_bookings' => [
        [
            'id' => 1,
            'court_name' => 'Center Court',
            'user_email' => 'john@example.com',
            'start_datetime' => '2026-01-27 14:00:00',
            'duration_hours' => 2,
            'lock_expires_at' => '2026-01-27 13:10:00',
            'created_at' => '2026-01-27 13:00:00'
        ],
        // ... more locked bookings
    ]
]
```

**UI Requirements**:
- Table view with locked bookings
- Show expiration countdown
- "Cancel Booking" button for each

---

### DELETE /admin/bookings/{id}
**Purpose**: Cancel locked booking  
**Middleware**: auth, admin  
**Controller**: Admin\BookingController@destroy  
**Path Parameters**: `id` (booking ID)

**Business Logic**:
1. Verify booking status='locked' (cannot cancel confirmed)
2. Delete booking record (releases time slot)

**Success Response**: Redirect back with "Booking cancelled" message  
**Error Response**: 403 Forbidden - "Cannot cancel confirmed booking"

---

## Error Handling

### Standard Error Responses
- 401 Unauthorized: Redirect to `/login`
- 403 Forbidden: Display "Access Denied" error page
- 404 Not Found: Display custom 404 page
- 422 Validation Error: Redirect back with error messages
- 500 Server Error: Display generic error page (log detailed error)

### Validation Error Format
```php
[
    'errors' => [
        'email' => ['The email has already been taken.'],
        'password' => ['The password must be at least 8 characters.']
    ]
]
```

---

## Middleware Summary

| Middleware | Purpose | Routes |
|------------|---------|--------|
| guest | Unauthenticated users only | /login, /register |
| auth | Authenticated users only | /courts/{id}, /bookings/*, /logout |
| admin | Admin users only | /admin/* |

---

**Status**: All route contracts defined. Ready for implementation.
