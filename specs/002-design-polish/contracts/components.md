# Component Contracts: UI/UX Design Polish

**Feature**: 002-design-polish  
**Phase**: 1 (Design & Contracts)  
**Date**: 2026-01-28

## Overview

This document defines the API contracts for all Blade components in the design system. Each contract specifies props, usage examples, and expected output.

---

## 1. Button Component Contract

**File**: `resources/views/components/button.blade.php`

### Props API

| Prop | Type | Default | Required | Values | Description |
|------|------|---------|----------|--------|-------------|
| `variant` | string | `'primary'` | No | `primary`, `secondary`, `danger`, `link` | Visual style variant |
| `type` | string | `'button'` | No | `button`, `submit`, `reset` | HTML button type |
| `loading` | boolean | `false` | No | `true`, `false` | Shows spinner, disables button |
| `disabled` | boolean | `false` | No | `true`, `false` | Disables button interaction |
| `href` | string | `null` | No | URL string | Converts to link (`<a>` tag) |

### Usage Examples

```blade
{{-- Primary button (default) --}}
<x-button>Save Court</x-button>

{{-- Secondary button --}}
<x-button variant="secondary">Cancel</x-button>

{{-- Danger button --}}
<x-button variant="danger" type="submit">Delete Booking</x-button>

{{-- Link button --}}
<x-button variant="link" href="{{ route('courts.index') }}">Back to Courts</x-button>

{{-- Loading state --}}
<x-button :loading="$isProcessing">Processing Payment...</x-button>

{{-- Disabled state --}}
<x-button :disabled="!$canBook">Book Now</x-button>
```

### Expected Output

```html
<!-- Primary button -->
<button type="button" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-150 shadow-sm hover:shadow-md transform hover:scale-105">
    Save Court
</button>

<!-- Loading button -->
<button type="button" disabled class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md cursor-wait opacity-75">
    <svg class="animate-spin h-4 w-4 mr-2" ...></svg>
    Processing Payment...
</button>

<!-- Link button -->
<a href="/courts" class="inline-flex items-center px-3 py-1.5 text-blue-500 text-sm font-medium hover:text-blue-600 hover:underline transition-colors duration-150">
    Back to Courts
</a>
```

---

## 2. Form Input Component Contract

**File**: `resources/views/components/form-input.blade.php`

### Props API

| Prop | Type | Default | Required | Values | Description |
|------|------|---------|----------|--------|-------------|
| `type` | string | `'text'` | No | `text`, `email`, `password`, `number`, `tel`, `url` | HTML input type |
| `name` | string | `''` | **Yes** | Any valid name | Input name attribute |
| `label` | string | `null` | No | Any string | Label text (renders above input) |
| `placeholder` | string | `''` | No | Any string | Placeholder text |
| `required` | boolean | `false` | No | `true`, `false` | Adds asterisk to label |
| `disabled` | boolean | `false` | No | `true`, `false` | Disables input |
| `value` | string/number | `''` | No | Any value | Pre-filled value |
| `error` | string | `null` | No | Error message | Shows error state and message |

### Usage Examples

```blade
{{-- Simple text input --}}
<x-form-input 
    name="court_name" 
    label="Court Name" 
    placeholder="Enter court name"
/>

{{-- Required input --}}
<x-form-input 
    name="email" 
    type="email" 
    label="Email Address" 
    :required="true"
    :value="old('email')"
/>

{{-- Input with error --}}
<x-form-input 
    name="price" 
    type="number" 
    label="Hourly Price" 
    :value="old('price')"
    :error="$errors->first('price')"
/>

{{-- Disabled input --}}
<x-form-input 
    name="booking_id" 
    label="Booking ID" 
    :value="$booking->id"
    :disabled="true"
/>
```

### Expected Output

```html
<!-- Input with error -->
<div class="mb-4">
    <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
        Hourly Price
    </label>
    <input 
        type="number" 
        name="price" 
        id="price"
        value="25.00"
        class="block w-full px-4 py-2 border border-red-500 rounded-md text-gray-700 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all"
    />
    <div class="flex items-center gap-1 mt-1 text-sm text-red-600">
        <svg class="w-4 h-4" ...></svg>
        <span>The price field is required.</span>
    </div>
</div>
```

---

## 3. Card Component Contract

**File**: `resources/views/components/card.blade.php`

### Props API

| Prop | Type | Default | Required | Values | Description |
|------|------|---------|----------|--------|-------------|
| `variant` | string | `'default'` | No | `default`, `stat`, `table` | Card style variant |
| `accent` | string | `null` | No | `blue`, `green`, `red`, `orange`, `gray` | Colored left border (stat only) |
| `hoverable` | boolean | `false` | No | `true`, `false` | Adds hover effect |

### Usage Examples

```blade
{{-- Default card --}}
<x-card>
    <h3 class="text-xl font-bold mb-2">Center Court</h3>
    <p class="text-gray-600">Premium tennis court</p>
</x-card>

{{-- Stat card with accent --}}
<x-card variant="stat" accent="blue">
    <div class="flex items-center">
        <div class="flex-1">
            <p class="text-sm text-gray-600">Total Courts</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['total_courts'] }}</p>
        </div>
        <div class="text-blue-500">
            <svg class="w-12 h-12" ...></svg>
        </div>
    </div>
</x-card>

{{-- Hoverable court card --}}
<x-card :hoverable="true">
    <img src="..." class="w-full h-48 object-cover -mx-6 -mt-6 mb-4">
    <h3>{{ $court->name }}</h3>
    <x-button variant="link" href="{{ route('courts.show', $court) }}">
        View Details
    </x-button>
</x-card>

{{-- Table wrapper card --}}
<x-card variant="table">
    <table class="w-full">
        <thead>...</thead>
        <tbody>...</tbody>
    </table>
</x-card>
```

### Expected Output

```html
<!-- Default card -->
<div class="bg-white rounded-lg shadow-md p-6 border border-gray-100">
    <h3 class="text-xl font-bold mb-2">Center Court</h3>
    <p class="text-gray-600">Premium tennis court</p>
</div>

<!-- Stat card with blue accent -->
<div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
    <div class="flex items-center">
        ...
    </div>
</div>

<!-- Hoverable card -->
<div class="bg-white rounded-lg shadow-md p-6 border border-gray-100 hover:shadow-lg hover:scale-102 transition-all duration-200 cursor-pointer">
    ...
</div>
```

---

## 4. Badge Component Contract

**File**: `resources/views/components/badge.blade.php`

### Props API

| Prop | Type | Default | Required | Values | Description |
|------|------|---------|----------|--------|-------------|
| `status` | string | `'default'` | No | `available`, `booked`, `locked`, `confirmed`, `cancelled`, `active`, `disabled` | Status type |
| `size` | string | `'default'` | No | `small`, `default` | Badge size |

### Usage Examples

```blade
{{-- Available status --}}
<x-badge status="available">Available</x-badge>

{{-- Booking statuses --}}
<x-badge status="confirmed">Confirmed</x-badge>
<x-badge status="locked">Locked</x-badge>
<x-badge status="cancelled">Cancelled</x-badge>

{{-- Court statuses --}}
<x-badge status="active">Active</x-badge>
<x-badge status="disabled">Disabled</x-badge>

{{-- Small badge --}}
<x-badge status="booked" size="small">Booked</x-badge>

{{-- Dynamic status --}}
<x-badge :status="$booking->status">
    {{ ucfirst($booking->status) }}
</x-badge>
```

### Expected Output

```html
<!-- Available badge -->
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
    Available
</span>

<!-- Locked badge -->
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-300">
    Locked
</span>

<!-- Small badge -->
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600 border border-gray-300">
    Booked
</span>
```

---

## 5. Loading Spinner Component Contract

**File**: `resources/views/components/loading-spinner.blade.php`

### Props API

| Prop | Type | Default | Required | Values | Description |
|------|------|---------|----------|--------|-------------|
| `size` | string | `'default'` | No | `small`, `default`, `large` | Spinner size |
| `color` | string | `'current'` | No | `current`, `white`, `blue` | Spinner color |

### Usage Examples

```blade
{{-- Default spinner --}}
<x-loading-spinner />

{{-- Small inline spinner --}}
<span>Loading... <x-loading-spinner size="small" /></span>

{{-- White spinner for colored backgrounds --}}
<button class="bg-blue-500 text-white px-4 py-2">
    <x-loading-spinner size="small" color="white" />
    Processing
</button>

{{-- Large page spinner --}}
<div class="flex justify-center items-center h-64">
    <x-loading-spinner size="large" color="blue" />
</div>
```

### Expected Output

```html
<!-- Default spinner -->
<svg class="animate-spin w-6 h-6 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>

<!-- Small white spinner -->
<svg class="animate-spin w-4 h-4 text-white" ...></svg>
```

---

## Component Composition Patterns

### Form with Validation

```blade
<form method="POST" action="{{ route('admin.courts.store') }}">
    @csrf
    
    <x-form-input 
        name="name" 
        label="Court Name" 
        :required="true"
        :value="old('name')"
        :error="$errors->first('name')"
    />
    
    <x-form-input 
        name="hourly_price" 
        type="number" 
        label="Hourly Price" 
        :required="true"
        :value="old('hourly_price')"
        :error="$errors->first('hourly_price')"
        placeholder="25.00"
    />
    
    <div class="flex gap-4">
        <x-button type="submit">Create Court</x-button>
        <x-button variant="secondary" href="{{ route('admin.courts.index') }}">
            Cancel
        </x-button>
    </div>
</form>
```

### Card Grid with Badges

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($courts as $court)
        <x-card :hoverable="true">
            <img src="{{ $court->photo_url }}" class="w-full h-48 object-cover -mx-6 -mt-6 mb-4">
            
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xl font-bold">{{ $court->name }}</h3>
                <x-badge :status="$court->status">
                    {{ ucfirst($court->status) }}
                </x-badge>
            </div>
            
            <p class="text-gray-600 text-sm mb-4">{{ $court->description }}</p>
            
            <x-button 
                variant="primary" 
                href="{{ route('courts.show', $court) }}"
            >
                View Details
            </x-button>
        </x-card>
    @endforeach
</div>
```

### Admin Stats Dashboard

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <x-card variant="stat" accent="blue">
        <p class="text-sm text-gray-600">Total Courts</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_courts'] }}</p>
    </x-card>
    
    <x-card variant="stat" accent="green">
        <p class="text-sm text-gray-600">Total Bookings</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_bookings'] }}</p>
    </x-card>
    
    <x-card variant="stat" accent="orange">
        <p class="text-sm text-gray-600">Locked Bookings</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['locked_bookings'] }}</p>
    </x-card>
    
    <x-card variant="stat" accent="red">
        <p class="text-sm text-gray-600">Revenue</p>
        <p class="text-3xl font-bold text-gray-800">${{ number_format($stats['revenue'], 2) }}</p>
    </x-card>
</div>
```

### Button with Loading State

```blade
<form method="POST" action="{{ route('bookings.payment', $booking) }}" x-data="{ processing: false }" @submit="processing = true">
    @csrf
    
    <x-form-input 
        name="amount" 
        type="number" 
        label="Payment Amount" 
        :value="$booking->total_price"
    />
    
    <x-button 
        type="submit" 
        x-bind:loading="processing"
    >
        <span x-show="!processing">Process Payment</span>
        <span x-show="processing">Processing...</span>
    </x-button>
</form>
```

## Backward Compatibility

All components are **new** - no breaking changes to existing code. Migration to components is **optional** and can be done incrementally:

1. **Phase 1**: Create all components
2. **Phase 2**: Update high-traffic pages (courts listing, booking flow)
3. **Phase 3**: Update admin panel
4. **Phase 4**: Update auth pages
5. **Phase 5**: Remove old non-component code

Existing pages continue to work without components during migration.

## Error Handling

Components throw exceptions for invalid props during development:

```php
// In button.blade.php
@php
    $allowedVariants = ['primary', 'secondary', 'danger', 'link'];
    if (!in_array($variant, $allowedVariants)) {
        throw new \InvalidArgumentException("Invalid button variant '{$variant}'. Allowed: " . implode(', ', $allowedVariants));
    }
@endphp
```

This fails fast during development but should never happen in production if contracts are followed.
