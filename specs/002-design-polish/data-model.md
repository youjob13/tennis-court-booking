# Data Model: UI/UX Design Polish

**Feature**: 002-design-polish  
**Phase**: 1 (Design & Contracts)  
**Date**: 2026-01-28

## Overview

This document defines the structure of reusable Blade components that form the design system. No database entities are involved - this is purely presentation layer architecture.

## Component Structures

### 1. Button Component

**File**: `resources/views/components/button.blade.php`

**Purpose**: Unified button styling across all pages with consistent variants and states

**Props**:
```php
@props([
    'variant' => 'primary',    // primary|secondary|danger|link
    'type' => 'button',        // button|submit|reset
    'loading' => false,        // boolean
    'disabled' => false,       // boolean
    'href' => null,            // string (converts to <a> tag if provided)
])
```

**Variants**:
- **primary**: Blue background, white text - for main actions (Book Now, Save, Submit Payment)
- **secondary**: Gray background, dark gray text - for alternative actions (Cancel, Back, View)
- **danger**: Red background, white text - for destructive actions (Delete, Cancel Booking)
- **link**: Minimal style, blue underlined text - for tertiary actions (Learn More, View Details)

**States**:
- **default**: Base styling with shadow
- **hover**: Darker shade, increased shadow, slight scale (1.05)
- **active**: Even darker, reduced shadow, normal scale
- **disabled**: Gray background (bg-gray-300) with 50% opacity, not-allowed cursor (overrides variant color)
- **loading**: Shows spinner, disabled interaction, 75% opacity

**Validation Rules**:
- `variant` must be one of: primary, secondary, danger, link
- `type` must be one of: button, submit, reset
- `loading` and `disabled` must be boolean
- If `href` is provided, renders `<a>` tag instead of `<button>`

---

### 2. Form Input Component

**File**: `resources/views/components/form-input.blade.php`

**Purpose**: Consistent text input styling with validation states

**Props**:
```php
@props([
    'type' => 'text',          // text|email|password|number|tel|url
    'name' => '',              // string (required)
    'label' => null,           // string
    'placeholder' => '',       // string
    'required' => false,       // boolean
    'disabled' => false,       // boolean
    'value' => '',             // string|number
    'error' => null,           // string (error message)
])
```

**States**:
- **default**: Gray border, white background, blue focus ring
- **error**: Red border, red focus ring, shows error message below
- **disabled**: Gray background, not-allowed cursor
- **focus**: Blue ring, border color changes to blue

**Validation Rules**:
- `type` must be valid HTML input type
- `name` is required
- `error` triggers error state styling
- `required` adds red asterisk to label

**Rendered Structure**:
```html
<div>
    @if($label)
        <label>{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
    @endif
    <input ... />
    @if($error)
        <p class="error">{{ $error }}</p>
    @endif
</div>
```

---

### 3. Form Label Component

**File**: `resources/views/components/form-label.blade.php`

**Purpose**: Consistent label styling with required indicator

**Props**:
```php
@props([
    'for' => '',               // string (input ID)
    'required' => false,       // boolean
])
```

**Rendered Structure**:
```html
<label for="{{ $for }}" class="block text-sm font-semibold text-gray-700 mb-2">
    {{ $slot }}
    @if($required)<span class="text-red-500 ml-1">*</span>@endif
</label>
```

---

### 4. Form Error Component

**File**: `resources/views/components/form-error.blade.php`

**Purpose**: Consistent error message display

**Props**:
```php
@props([
    'message' => null,         // string
])
```

**Rendered Structure**:
```html
@if($message)
<div class="flex items-center gap-1 mt-1 text-sm text-red-600">
    <svg class="w-4 h-4" ...><!-- exclamation icon --></svg>
    <span>{{ $message }}</span>
</div>
@endif
```

---

### 5. Card Component

**File**: `resources/views/components/card.blade.php`

**Purpose**: Container with consistent shadow, padding, and rounded corners

**Props**:
```php
@props([
    'variant' => 'default',    // default|stat|table
    'accent' => null,          // blue|green|red|orange|gray (for stat variant)
    'hoverable' => false,      // boolean (adds hover effect)
])
```

**Variants**:
- **default**: White background, medium shadow, 24px padding, 8px rounded corners
- **stat**: Default + colored left border (4px) + icon placement
- **table**: No padding (table handles internal padding), hidden overflow

**States**:
- **hover** (if hoverable): Increased shadow, slight scale (1.02)

**Validation Rules**:
- `variant` must be one of: default, stat, table
- `accent` must be one of: blue, green, red, orange, gray (only used with stat variant)

---

### 6. Badge Component

**File**: `resources/views/components/badge.blade.php`

**Purpose**: Status indicators with consistent color coding

**Props**:
```php
@props([
    'status' => 'default',     // available|booked|locked|confirmed|cancelled|active|disabled
    'size' => 'default',       // small|default
])
```

**Status Colors**:
- **available**: Green background/text/border (bg-green-100 text-green-800 border-green-300)
- **booked**: Gray background/text/border (bg-gray-200 text-gray-600 border-gray-300)
- **locked**: Orange background/text/border (bg-orange-100 text-orange-800 border-orange-300)
- **confirmed**: Blue background/text/border (bg-blue-100 text-blue-800 border-blue-300)
- **cancelled**: Red background/text/border (bg-red-100 text-red-800 border-red-300)
- **active**: Green (same as available)
- **disabled**: Gray (same as booked)

**Sizes**:
- **small**: px-2 py-0.5 text-xs
- **default**: px-3 py-1 text-xs

**Validation Rules**:
- `status` must be one of the predefined status types
- `size` must be small or default

---

### 7. Loading Spinner Component

**File**: `resources/views/components/loading-spinner.blade.php`

**Purpose**: Animated spinner for loading states

**Props**:
```php
@props([
    'size' => 'default',       // small|default|large
    'color' => 'current',      // current|white|blue
])
```

**Sizes**:
- **small**: w-4 h-4 (16px) - for inline with text
- **default**: w-6 h-6 (24px) - for buttons
- **large**: w-12 h-12 (48px) - for page loading

**Colors**:
- **current**: Uses current text color (text-current)
- **white**: White color (text-white) - for colored buttons
- **blue**: Blue color (text-blue-500) - for standalone

**Rendered Structure**:
```html
<svg class="animate-spin {{ $sizeClass }} {{ $colorClass }}" xmlns="..." fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="..."></path>
</svg>
```

---

## Component Relationships

```
Button Component
├── Can contain Loading Spinner (when loading=true)
└── Used in: All pages (courts, bookings, admin, auth)

Form Input Component
├── Uses Form Label Component (when label provided)
├── Uses Form Error Component (when error provided)
└── Used in: Auth forms, booking forms, admin forms

Card Component
├── Container for: Court cards, stat cards, table wrappers
└── Used in: Courts listing, admin dashboard, admin tables

Badge Component
├── Standalone status indicator
└── Used in: Court availability, booking status, admin tables

Loading Spinner Component
├── Embedded in Button Component
└── Used standalone in: Payment processing, form submissions
```

## Naming Conventions

**Component Files**: kebab-case with `.blade.php` extension
- ✅ `button.blade.php`
- ✅ `form-input.blade.php`
- ✅ `loading-spinner.blade.php`
- ❌ `ButtonComponent.blade.php`
- ❌ `form_input.blade.php`

**Component Usage**: `<x-component-name>`
- ✅ `<x-button>`
- ✅ `<x-form-input>`
- ✅ `<x-loading-spinner>`

**Props**: camelCase in PHP, kebab-case in HTML
```blade
{{-- PHP prop definition --}}
@props(['variant' => 'primary'])

{{-- HTML usage --}}
<x-button variant="primary">Submit</x-button>
```

**CSS Classes**: Tailwind utilities only, no custom CSS classes
- ✅ `bg-blue-500 text-white px-4 py-2 rounded-md`
- ❌ `btn btn-primary`
- ❌ Custom classes in app.css

## State Management

Components are **stateless** - they receive props and render output. State management remains in:
- **Blade directives**: `@if`, `@foreach`, `@error`
- **Alpine.js**: Minimal interactions (dropdowns, modals)
- **Laravel sessions**: Flash messages, validation errors
- **Form submissions**: Standard POST requests

No JavaScript state management libraries added (Vue, React, Alpine Store).

## Validation

All components validate props in their Blade files:
```blade
@php
    $allowedVariants = ['primary', 'secondary', 'danger', 'link'];
    if (!in_array($variant, $allowedVariants)) {
        throw new InvalidArgumentException("Invalid button variant: {$variant}");
    }
@endphp
```

## Accessibility

All components rely on semantic HTML without ARIA attributes:
- **Semantic HTML**: `<button>` not `<div>`, `<label>` with `for` attribute, proper heading hierarchy
- **Focus management**: Visible focus rings (focus:ring-2 focus:ring-blue-500)
- **Color contrast**: All text meets WCAG 2.1 Level AA (4.5:1 ratio)
- **Touch targets**: Minimum 44x44px for interactive elements on mobile

## Testing Strategy

**Manual testing checklist** for each component:
1. Visual: Does it match design system spec?
2. Variants: Do all variants render correctly?
3. States: Do hover, active, disabled states work?
4. Responsive: Does it work on mobile (320px) and desktop (1920px)?
5. Accessibility: Can it be used with keyboard only?
6. Integration: Does it work in all pages where used?

No automated component tests in MVP phase (per constitution).
