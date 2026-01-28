# Quickstart Guide: Design System Implementation

**Feature**: 002-design-polish  
**Date**: 2026-01-28  
**Audience**: Developers implementing the design system

## Overview

This guide walks through implementing the tennis court booking design system. The system consists of 7 reusable Blade components and consistent Tailwind CSS patterns across all pages.

**Time Estimate**: 6-8 hours for complete implementation
**Prerequisites**: Laravel 11.x with Breeze, Tailwind CSS 3.x configured

---

## Quick Reference

### Component Cheat Sheet

```blade
{{-- Buttons --}}
<x-button variant="primary">Save</x-button>
<x-button variant="secondary">Cancel</x-button>
<x-button variant="danger">Delete</x-button>
<x-button variant="link" href="/path">Link</x-button>

{{-- Forms --}}
<x-form-input name="email" label="Email" type="email" :required="true" />
<x-form-label for="name" :required="true">Court Name</x-form-label>
<x-form-error :message="$errors->first('name')" />

{{-- Cards --}}
<x-card>Content</x-card>
<x-card variant="stat" accent="blue">Stats</x-card>
<x-card :hoverable="true">Clickable card</x-card>

{{-- Badges --}}
<x-badge status="available">Available</x-badge>
<x-badge status="confirmed">Confirmed</x-badge>

{{-- Loading --}}
<x-loading-spinner size="small" color="white" />
```

### Color Palette Quick Reference

```
Primary Blue:   bg-blue-500    #3B82F6
Secondary Gray: bg-gray-200    #E5E7EB
Success Green:  bg-green-500   #10B981
Warning Orange: bg-orange-500  #F59E0B
Danger Red:     bg-red-500     #EF4444
Text Dark:      text-gray-800  #1F2937
Text Body:      text-gray-700  #374151
Text Light:     text-gray-500  #6B7280
```

---

## Implementation Phases

### Phase 1: Create Components (2 hours)

**Create these files in `resources/views/components/`:**

1. `button.blade.php` - Button with 4 variants, 5 states
2. `form-input.blade.php` - Input with label, validation, error handling
3. `form-label.blade.php` - Consistent label with required indicator
4. `form-error.blade.php` - Error message with icon
5. `card.blade.php` - Container with 3 variants
6. `badge.blade.php` - Status badge with 7 status types
7. `loading-spinner.blade.php` - Animated SVG spinner

**Each component follows this structure:**
```blade
@props([
    'variant' => 'default',
    // ... other props with defaults
])

@php
    // Validation logic
    // Class generation logic
@endphp

<element {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</element>
```

**See**: [data-model.md](data-model.md) for complete component specifications
**See**: [contracts/components.md](contracts/components.md) for prop APIs

---

### Phase 2: Update Court Pages (1.5 hours)

#### 2.1 Courts Index (`resources/views/courts/index.blade.php`)

**Changes:**
- Replace inline button styles with `<x-button>`
- Wrap court cards in `<x-card :hoverable="true">`
- Replace availability badges with `<x-badge status="...">`
- Update grid spacing to `gap-6`

**Before:**
```blade
<button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
    View Details
</button>
```

**After:**
```blade
<x-button variant="primary" href="{{ route('courts.show', $court) }}">
    View Details
</x-button>
```

#### 2.2 Court Detail (`resources/views/courts/show.blade.php`)

**Changes:**
- Replace booking button with `<x-button variant="primary">`
- Use `<x-badge>` for slot statuses
- Wrap form sections in `<x-card>`

---

### Phase 3: Update Booking Flow (1.5 hours)

#### 3.1 Payment Page (`resources/views/bookings/payment.blade.php`)

**Changes:**
- Replace form inputs with `<x-form-input>`
- Add loading spinner to payment button
- Use `<x-card>` for payment form container
- Show `<x-badge status="locked">` for booking status

**Payment button with loading:**
```blade
<x-button 
    type="submit" 
    :loading="$processing ?? false"
>
    <span x-show="!processing">Process Payment</span>
    <span x-show="processing">Processing...</span>
</x-button>
```

#### 3.2 Confirmation Page (`resources/views/bookings/confirmation.blade.php`)

**Changes:**
- Use `<x-card>` for confirmation details
- Replace status badge with `<x-badge status="confirmed">`
- Update "Back to Courts" link to `<x-button variant="link">`

---

### Phase 4: Update Admin Panel (2 hours)

#### 4.1 Admin Dashboard (`resources/views/admin/dashboard.blade.php`)

**Changes:**
- Replace stat cards with `<x-card variant="stat" accent="...">`
- Use `<x-badge>` for booking statuses in recent bookings table
- Replace action buttons with `<x-button>` components

**Stats card example:**
```blade
<x-card variant="stat" accent="blue">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-600">Total Courts</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['total_courts'] }}</p>
        </div>
        <div class="text-blue-500">
            <svg class="w-12 h-12" ...></svg>
        </div>
    </div>
</x-card>
```

#### 4.2 Admin Courts Pages

**Index (`resources/views/admin/courts/index.blade.php`):**
- Wrap table in `<x-card variant="table">`
- Use `<x-badge>` for court status
- Replace buttons with `<x-button variant="danger">` for delete

**Create (`resources/views/admin/courts/create.blade.php`):**
- Replace all inputs with `<x-form-input>`
- Show validation errors with `:error="$errors->first('field')"`
- Update buttons to `<x-button type="submit">` and `<x-button variant="secondary">`

#### 4.3 Admin Bookings Page

**Changes:**
- Wrap table in `<x-card variant="table">`
- Use `<x-badge>` for all booking statuses
- Replace cancel buttons with `<x-button variant="danger" size="small">`

---

### Phase 5: Update Auth Pages (1 hour)

#### 5.1 Login (`resources/views/auth/login.blade.php`)

**Changes:**
- Replace email/password inputs with `<x-form-input>`
- Update submit button to `<x-button type="submit">`
- Update "Forgot password?" link to `<x-button variant="link">`

#### 5.2 Register (`resources/views/auth/register.blade.php`)

**Changes:**
- Replace all form inputs with `<x-form-input>`
- Show validation errors: `:error="$errors->first('field')"`
- Update submit button to `<x-button type="submit">`
- Update "Already registered?" link to `<x-button variant="link">`

---

## Testing Checklist

### Visual Testing

For each updated page, verify:

- [ ] **Colors match palette**: Blue primary, gray secondary, red danger
- [ ] **Spacing is consistent**: 4/8/16/24/32px between elements
- [ ] **Typography is readable**: Text sizes and weights match hierarchy
- [ ] **Cards have shadows**: Subtle shadow on all cards
- [ ] **Badges are color-coded**: Green=available, orange=locked, red=cancelled

### Interactive Testing

For all buttons and forms, verify:

- [ ] **Hover effects work**: Buttons darken and scale slightly on hover
- [ ] **Loading states show**: Spinner appears, button disables during async
- [ ] **Disabled states are clear**: Gray background, not-allowed cursor
- [ ] **Form validation displays**: Red borders and error messages below inputs
- [ ] **Focus rings visible**: Blue ring appears on keyboard focus

### Responsive Testing

Test on these viewport widths:

- [ ] **Mobile (375px)**: Single column layout, touch targets 44px min
- [ ] **Tablet (768px)**: Two column grid for cards
- [ ] **Desktop (1280px)**: Three column grid for cards, full table width

### Accessibility Testing

- [ ] **Keyboard navigation**: Tab through all interactive elements
- [ ] **Form labels**: All inputs have associated labels
- [ ] **Error announcements**: Screen readers can access error messages
- [ ] **Color contrast**: Text meets 4.5:1 ratio on all backgrounds

---

## Common Patterns

### Form with Validation

```blade
<form method="POST" action="...">
    @csrf
    
    <x-form-input 
        name="name" 
        label="Court Name" 
        :required="true"
        :value="old('name', $court->name ?? '')"
        :error="$errors->first('name')"
        placeholder="Enter court name"
    />
    
    <x-form-input 
        name="price" 
        type="number" 
        label="Hourly Price" 
        :required="true"
        :value="old('price', $court->hourly_price ?? '')"
        :error="$errors->first('price')"
        placeholder="25.00"
    />
    
    <div class="flex gap-4">
        <x-button type="submit">Save Court</x-button>
        <x-button variant="secondary" href="{{ route('admin.courts.index') }}">
            Cancel
        </x-button>
    </div>
</form>
```

### Table with Actions

```blade
<x-card variant="table">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Court</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($courts as $court)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 text-sm text-gray-800">{{ $court->name }}</td>
                <td class="px-6 py-4">
                    <x-badge :status="$court->status">
                        {{ ucfirst($court->status) }}
                    </x-badge>
                </td>
                <td class="px-6 py-4 text-right">
                    <x-button variant="link" href="{{ route('admin.courts.edit', $court) }}">
                        Edit
                    </x-button>
                    <form method="POST" action="{{ route('admin.courts.destroy', $court) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <x-button variant="danger" type="submit">Delete</x-button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</x-card>
```

### Stats Grid

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-card variant="stat" accent="blue">
        <p class="text-sm text-gray-600">Total Courts</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['courts'] }}</p>
    </x-card>
    
    <x-card variant="stat" accent="green">
        <p class="text-sm text-gray-600">Active Bookings</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['bookings'] }}</p>
    </x-card>
    
    <x-card variant="stat" accent="orange">
        <p class="text-sm text-gray-600">Revenue Today</p>
        <p class="text-3xl font-bold text-gray-800">${{ number_format($stats['revenue'], 2) }}</p>
    </x-card>
    
    <x-card variant="stat" accent="red">
        <p class="text-sm text-gray-600">Pending</p>
        <p class="text-3xl font-bold text-gray-800">{{ $stats['pending'] }}</p>
    </x-card>
</div>
```

---

## Troubleshooting

### Component Not Found

**Error**: `View [components.button] not found`

**Fix**: Ensure component file exists at `resources/views/components/button.blade.php`

### Props Not Working

**Error**: Undefined variable `$variant`

**Fix**: Ensure `@props([...])` directive is at top of component file before any PHP code

### Styles Not Applying

**Issue**: Component renders but styles don't show

**Fix**: 
1. Run `npm run build` to rebuild Tailwind CSS
2. Check `tailwind.config.js` includes `./resources/**/*.blade.php` in content array
3. Clear browser cache

### Merge Conflict with Attributes

**Issue**: Custom classes override component classes

**Fix**: Use `{{ $attributes->merge(['class' => '...']) }}` instead of `{{ $attributes }}`

---

## Performance Considerations

**Component Overhead**: Blade components add negligible overhead (<1ms per component). With 50+ components per page, total overhead is ~50ms.

**CSS Bundle Size**: Tailwind purges unused classes. Adding components does not increase CSS size if classes are already used elsewhere.

**Caching**: Blade compiles components to cached PHP. No compilation happens on subsequent requests.

**Optimization**: None needed for MVP. Component rendering is fast enough for production use.

---

## Migration Strategy

**Incremental Migration** (recommended):
1. Create all components (Day 1)
2. Update one page at a time (Days 2-5)
3. Test each page before moving to next
4. Old and new styles coexist during migration

**Big Bang Migration** (not recommended):
1. Create all components (Day 1)
2. Update all pages at once (Day 2-3)
3. High risk of introducing bugs

**Rollback Plan**:
- Components are additive - old code continues to work
- If component has bugs, temporarily revert that page to old style
- Fix component, then re-migrate page

---

## Resources

- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Laravel Blade Components](https://laravel.com/docs/blade#components)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

## Next Steps

After completing this guide:
1. Run visual testing checklist
2. Run interactive testing checklist
3. Run responsive testing checklist
4. Get user feedback on visual consistency
5. Iterate on component variants based on feedback
