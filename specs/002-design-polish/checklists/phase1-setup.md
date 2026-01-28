# Phase 1 Setup Checklist: Design System Foundation

**Feature**: 002-design-polish  
**Phase**: 1 (Setup)  
**Purpose**: Create 7 reusable Blade components that form the design system foundation

## Component Creation Tasks

### T001 - Button Component
- [ ] Create file `resources/views/components/button.blade.php`
- [ ] Define props: variant (primary/secondary/danger/link), type (button/submit/reset), loading (bool), disabled (bool), href (string)
- [ ] Implement prop validation for allowed variant values
- [ ] Create CSS classes for primary variant: bg-blue-500, hover:bg-blue-600, active:bg-blue-700, text-white
- [ ] Create CSS classes for secondary variant: bg-gray-200, hover:bg-gray-300, active:bg-gray-400, text-gray-700
- [ ] Create CSS classes for danger variant: bg-red-500, hover:bg-red-600, active:bg-red-700, text-white
- [ ] Create CSS classes for link variant: text-blue-500, hover:text-blue-600, hover:underline
- [ ] Implement disabled state: opacity-50, cursor-not-allowed, pointer-events-none
- [ ] Implement loading state: include loading spinner, disabled=true, cursor-wait
- [ ] Add transition classes: transition-all duration-150
- [ ] Add hover effects: shadow-md, transform scale-105
- [ ] Handle href prop: render `<a>` tag instead of `<button>` when href provided
- [ ] Test all 4 variants × 5 states = 20 combinations

### T002 - Form Input Component
- [ ] Create file `resources/views/components/form-input.blade.php`
- [ ] Define props: type, name (required), label, placeholder, required (bool), disabled (bool), value, error
- [ ] Render label above input if provided using Form Label component
- [ ] Create input with classes: w-full, px-4, py-2, border, border-gray-300, rounded-md, text-gray-700
- [ ] Add focus state: focus:ring-2, focus:ring-blue-500, focus:border-blue-500
- [ ] Add error state: border-red-500, focus:ring-red-500, focus:border-red-500
- [ ] Add disabled state: bg-gray-100, text-gray-500, cursor-not-allowed
- [ ] Render error message below input if provided using Form Error component
- [ ] Ensure min-height 44px for touch targets (py-2 gives 16px + text ≈ 44px)
- [ ] Support old() helper for repopulating values after validation errors
- [ ] Test with text, email, password, number, tel, url input types

### T003 - Form Label Component
- [ ] Create file `resources/views/components/form-label.blade.php`
- [ ] Define props: for (input id), required (bool)
- [ ] Create label with classes: block, text-sm, font-semibold, text-gray-700, mb-2
- [ ] Add red asterisk if required: `<span class="text-red-500 ml-1">*</span>`
- [ ] Ensure proper association with input via `for` attribute
- [ ] Test label renders slot content correctly

### T004 - Form Error Component
- [ ] Create file `resources/views/components/form-error.blade.php`
- [ ] Define props: message (string)
- [ ] Only render if message is not null/empty
- [ ] Create container with classes: flex, items-center, gap-1, mt-1, text-sm, text-red-600
- [ ] Add exclamation circle SVG icon (w-4 h-4)
- [ ] Render error message text
- [ ] Test error displays correctly below inputs
- [ ] Test error doesn't render when message is null

### T005 - Card Component
- [ ] Create file `resources/views/components/card.blade.php`
- [ ] Define props: variant (default/stat/table), accent (blue/green/red/orange/gray), hoverable (bool)
- [ ] Implement default variant: bg-white, rounded-lg, shadow-md, p-6, border, border-gray-100
- [ ] Implement stat variant: default classes + border-l-4, border-{accent}-500 (colored left border)
- [ ] Implement table variant: bg-white, rounded-lg, shadow-sm, overflow-hidden (no padding - table handles it)
- [ ] Add hoverable classes if enabled: hover:shadow-lg, hover:scale-102, transition-all duration-200, cursor-pointer
- [ ] Validate accent prop only used with stat variant
- [ ] Test all 3 variants with and without hoverable
- [ ] Test stat variant with all 5 accent colors

### T006 - Badge Component
- [ ] Create file `resources/views/components/badge.blade.php`
- [ ] Define props: status (available/booked/locked/confirmed/cancelled/active/disabled), size (small/default)
- [ ] Implement base classes: inline-flex, items-center, rounded-full, font-medium, border
- [ ] Implement default size: px-3, py-1, text-xs
- [ ] Implement small size: px-2, py-0.5, text-xs
- [ ] Implement available status: bg-green-100, text-green-800, border-green-300
- [ ] Implement booked status: bg-gray-200, text-gray-600, border-gray-300
- [ ] Implement locked status: bg-orange-100, text-orange-800, border-orange-300
- [ ] Implement confirmed status: bg-blue-100, text-blue-800, border-blue-300
- [ ] Implement cancelled status: bg-red-100, text-red-800, border-red-300
- [ ] Implement active status: same as available (green)
- [ ] Implement disabled status: same as booked (gray)
- [ ] Validate status prop against allowed values
- [ ] Test all 7 statuses × 2 sizes = 14 combinations

### T007 - Loading Spinner Component
- [ ] Create file `resources/views/components/loading-spinner.blade.php`
- [ ] Define props: size (small/default/large), color (current/white/blue)
- [ ] Implement small size: w-4, h-4
- [ ] Implement default size: w-6, h-6
- [ ] Implement large size: w-12, h-12
- [ ] Implement current color: text-current
- [ ] Implement white color: text-white
- [ ] Implement blue color: text-blue-500
- [ ] Create SVG spinner with animate-spin class
- [ ] Use circle with opacity-25 for background ring
- [ ] Use path with opacity-75 for spinning arc
- [ ] Test all 3 sizes × 3 colors = 9 combinations
- [ ] Verify animation is smooth (60fps)

## Component Quality Checks

### Code Clarity
- [ ] All component files have clear prop definitions at top using @props([...])
- [ ] Prop defaults are sensible and documented
- [ ] Component logic uses clear variable names (no abbreviations like $var, $cls)
- [ ] Validation errors throw clear exceptions with allowed values
- [ ] Each component file is under 100 lines (keeps components simple)

### Reusability
- [ ] Components accept slot content for flexible usage
- [ ] Components merge attributes allowing custom classes: `{{ $attributes->merge(['class' => $classes]) }}`
- [ ] Components don't make assumptions about parent context
- [ ] Components work in isolation without requiring specific wrapper elements

### Visual Consistency
- [ ] All components use consistent spacing scale (4px base: 1, 2, 3, 4, 6, 8, 12)
- [ ] All components use consistent color palette (blue-500, gray-200, green-500, orange-500, red-500)
- [ ] All components use consistent typography (text-xs, text-sm, font-medium, font-semibold)
- [ ] All hover/active transitions use consistent duration (150ms)

### Accessibility
- [ ] Buttons have proper type attribute (button/submit/reset)
- [ ] Form inputs have associated labels (via for attribute)
- [ ] Disabled states have cursor-not-allowed and aria-disabled
- [ ] Focus states have visible rings (focus:ring-2, focus:ring-{color}-500)
- [ ] Loading spinners include aria-hidden="true" (decorative only)
- [ ] Error messages are programmatically associated with inputs

### Mobile Responsiveness
- [ ] All interactive elements meet 44x44px minimum touch target
- [ ] Components use responsive padding (p-4 on mobile, p-6 on desktop if needed)
- [ ] Text sizes remain readable on small screens (minimum text-xs = 12px)
- [ ] No horizontal overflow on 320px viewport width

## Testing Checklist

### Visual Testing (Browser)
- [ ] Open example page with all components rendered
- [ ] Verify Button component shows all 4 variants with distinct colors
- [ ] Verify Form Input component renders with label and proper styling
- [ ] Verify Form Error component shows red text with icon
- [ ] Verify Card component shows shadow and padding
- [ ] Verify Badge component shows all 7 status colors correctly
- [ ] Verify Loading Spinner animates smoothly

### Interactive Testing
- [ ] Hover over buttons - verify darker shade and scale effect
- [ ] Click and hold button - verify active state (darker)
- [ ] Tab to button with keyboard - verify focus ring visible
- [ ] Hover over hoverable card - verify shadow increase and scale
- [ ] Focus form input - verify blue ring appears
- [ ] View loading button - verify spinner rotates

### Responsive Testing
- [ ] Test components at 320px width (small mobile)
- [ ] Test components at 375px width (iPhone)
- [ ] Test components at 768px width (tablet)
- [ ] Test components at 1280px width (desktop)
- [ ] Verify no horizontal scroll at any viewport
- [ ] Verify touch targets remain 44px minimum on mobile

### Browser Compatibility
- [ ] Test in Chrome (latest)
- [ ] Test in Firefox (latest)
- [ ] Test in Safari (latest)
- [ ] Test in Edge (latest)
- [ ] Verify Tailwind classes work in all browsers
- [ ] Verify animations/transitions work in all browsers

## Completion Criteria

**Phase 1 is complete when**:
- [ ] All 7 component files created in resources/views/components/
- [ ] All components pass visual testing checklist
- [ ] All components pass interactive testing checklist
- [ ] All components pass responsive testing checklist
- [ ] All components pass browser compatibility testing
- [ ] Example page exists showing all component variants (optional but recommended)
- [ ] Tailwind CSS rebuilt with `npm run build` to include new component classes
- [ ] No console errors or warnings when components render

**Estimated Time**: 2 hours for experienced Laravel/Tailwind developer

**Blockers**: None - all components can be created in parallel

**Ready for Phase 2**: ✅ Once all 7 components exist and pass quality checks, any user story (US1-US5) can begin implementation
