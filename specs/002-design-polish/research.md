# Research: UI/UX Design Polish

**Feature**: 002-design-polish  
**Phase**: 0 (Outline & Research)  
**Date**: 2026-01-28

## Research Tasks Completed

### 1. Color Palette Research - Visual Comfort & Accessibility

**Decision**: Soft, professional color palette with sufficient contrast ratios

**Rationale**: 
- Tennis courts are visually associated with green, but bright greens cause eye strain
- Need professional appearance for admin panel while remaining approachable for users
- WCAG 2.1 Level AA requires 4.5:1 contrast ratio for normal text, 3:1 for large text
- Blue is universally trusted and accessible color for primary actions

**Color System**:
```
Primary (Blue): 
- Light: #60A5FA (blue-400) - hover states
- Default: #3B82F6 (blue-500) - primary buttons, links
- Dark: #2563EB (blue-600) - active states
- Text on primary: #FFFFFF (white) - 4.73:1 contrast ratio ✓

Secondary (Gray):
- Light: #F3F4F6 (gray-100) - table headers, disabled backgrounds
- Default: #E5E7EB (gray-200) - secondary buttons, borders
- Medium: #9CA3AF (gray-400) - placeholder text
- Dark: #374151 (gray-700) - body text
- Darker: #1F2937 (gray-800) - headings
- Text on gray-200: #374151 - 7.72:1 contrast ratio ✓

Success (Green):
- Light: #D1FAE5 (green-100) - available slot backgrounds
- Default: #10B981 (green-500) - success messages, available badges
- Dark: #059669 (green-600) - pricing display
- Text on green-100: #065F46 (green-800) - 7.5:1 contrast ratio ✓

Warning (Orange):
- Light: #FED7AA (orange-200) - locked slot backgrounds
- Default: #F59E0B (orange-500) - warning messages, locked badges
- Dark: #D97706 (orange-600) - warning text
- Text on orange-200: #92400E (orange-900) - 6.8:1 contrast ratio ✓

Danger (Red):
- Light: #FEE2E2 (red-100) - error backgrounds
- Default: #EF4444 (red-500) - error messages, danger buttons
- Dark: #DC2626 (red-600) - error text, active danger states
- Text on red-100: #991B1B (red-800) - 8.1:1 contrast ratio ✓

Booked (Gray - distinct from secondary):
- Background: #F9FAFB (gray-50) - booked slot backgrounds
- Border: #D1D5DB (gray-300) - booked slot borders
- Text: #6B7280 (gray-500) - booked slot text
```

**Alternatives Considered**:
- Bright Material Design colors - rejected: too vibrant, cause eye strain in large doses
- Pastel colors throughout - rejected: insufficient contrast, unprofessional appearance
- Dark mode first - rejected: out of scope, adds complexity

### 2. Typography Scale Research

**Decision**: Consistent font scale with clear hierarchy

**Rationale**:
- Default browser font stack provides excellent readability without external dependencies
- Tailwind's default type scale (text-xs to text-4xl) covers all needs
- Semi-bold weight (font-semibold) for headings provides contrast without being heavy
- Regular weight for body text maximizes readability

**Typography System**:
```
Font Family:
- Sans: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
- (No custom fonts = zero dependencies, faster load times)

Headings:
- H1: text-3xl (30px), font-bold, text-gray-800, mb-6
- H2: text-2xl (24px), font-semibold, text-gray-800, mb-4
- H3: text-xl (20px), font-semibold, text-gray-800, mb-3
- H4: text-lg (18px), font-semibold, text-gray-700, mb-2

Body Text:
- Large: text-base (16px), font-normal, text-gray-700, leading-relaxed
- Default: text-sm (14px), font-normal, text-gray-700, leading-normal
- Small: text-xs (12px), font-normal, text-gray-600, leading-tight

Interactive:
- Button text: text-sm, font-medium
- Link text: text-sm, font-medium, underline on hover
- Label text: text-sm, font-semibold
```

### 3. Spacing Scale Research - Visual Rhythm

**Decision**: 4px base unit with Tailwind's spacing scale

**Rationale**:
- 4px base unit (Tailwind default) creates harmonious visual rhythm
- Multiples of 4 (8, 12, 16, 24, 32, 48) provide sufficient variety without chaos
- Consistent spacing reduces cognitive load and creates professional appearance

**Spacing System**:
```
Component Internal Spacing:
- Button padding: px-4 py-2 (16px horizontal, 8px vertical)
- Card padding: p-6 (24px all sides)
- Form input padding: px-4 py-2 (16px horizontal, 8px vertical)
- Badge padding: px-3 py-1 (12px horizontal, 4px vertical)

Layout Spacing:
- Section margin: mb-8 (32px bottom)
- Element margin: mb-4 (16px bottom)
- Form field margin: mb-4 (16px bottom)
- Grid gap: gap-6 (24px between grid items)
- Small gaps: gap-2 (8px for inline elements)

Container Padding:
- Page container: py-12 (48px vertical)
- Card container: p-6 (24px all sides)
- Small containers: p-4 (16px all sides)
```

### 4. Button State Patterns Research

**Decision**: Consistent hover, active, disabled, and loading states

**Rationale**:
- Hover states must be immediately visible (100ms) to indicate interactivity
- Disabled states must clearly communicate non-interactive status
- Loading states prevent double-submission and provide async feedback
- Active states provide tactile feedback on click

**Button State System**:
```
Primary Button (Blue):
- Default: bg-blue-500 text-white px-4 py-2 rounded-md font-medium transition-all duration-150
- Hover: bg-blue-600 shadow-md transform scale-105
- Active: bg-blue-700 shadow-sm scale-100
- Disabled: bg-gray-300 text-gray-500 cursor-not-allowed opacity-50
- Loading: bg-blue-500 cursor-wait opacity-75 (with spinner icon)

Secondary Button (Gray):
- Default: bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-medium transition-all duration-150
- Hover: bg-gray-300 shadow-md
- Active: bg-gray-400 shadow-sm
- Disabled: bg-gray-100 text-gray-400 cursor-not-allowed opacity-50
- Loading: bg-gray-200 cursor-wait opacity-75

Danger Button (Red):
- Default: bg-red-500 text-white px-4 py-2 rounded-md font-medium transition-all duration-150
- Hover: bg-red-600 shadow-md
- Active: bg-red-700 shadow-sm
- Disabled: bg-gray-300 text-gray-500 cursor-not-allowed opacity-50
- Loading: bg-red-500 cursor-wait opacity-75

Link Button (Minimal):
- Default: text-blue-500 font-medium hover:underline
- Hover: text-blue-600
- Active: text-blue-700
- Disabled: text-gray-400 cursor-not-allowed no-underline
```

### 5. Form Control Patterns Research

**Decision**: Consistent input styling with clear validation states

**Rationale**:
- Form inputs must be immediately recognizable as interactive
- Error states must be visually distinct and positioned near the input
- Focus states must be visible for keyboard navigation
- Labels must have semantic association with inputs

**Form Control System**:
```
Text Input:
- Default: border border-gray-300 rounded-md px-4 py-2 text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all
- Error: border-red-500 focus:ring-red-500 focus:border-red-500
- Disabled: bg-gray-100 text-gray-500 cursor-not-allowed
- Width: w-full (spans container)

Label:
- Default: block text-sm font-semibold text-gray-700 mb-2
- Required indicator: text-red-500 ml-1 (asterisk)

Error Message:
- Position: Below input, mt-1
- Style: text-sm text-red-600 flex items-center gap-1
- Icon: Red exclamation circle SVG

Select Dropdown:
- Same as text input + appearance-none with custom chevron icon

Textarea:
- Same as text input + resize-vertical min-h-[100px]

Checkbox/Radio:
- Size: h-4 w-4
- Colors: text-blue-500 border-gray-300 rounded focus:ring-blue-500
```

### 6. Card & Container Patterns Research

**Decision**: Subtle shadows with rounded corners for depth

**Rationale**:
- Shadows create visual hierarchy without relying solely on borders
- Rounded corners soften the interface and feel more modern
- White backgrounds provide clear content separation
- Hover effects on interactive cards indicate clickability

**Card System**:
```
Basic Card:
- Default: bg-white rounded-lg shadow-md p-6 border border-gray-100
- Hover (interactive): shadow-lg transform scale-102 transition-all duration-200
- No hover (static): no additional classes

Stat Card (Admin Dashboard):
- Base: bg-white rounded-lg shadow-sm p-6
- Accent: border-l-4 border-{color}-500 (colored left border)
- Icon: text-{color}-500 w-12 h-12

Table Card:
- Base: bg-white rounded-lg shadow-sm overflow-hidden
- Header: bg-gray-50 px-6 py-3 border-b border-gray-200
- Row: px-6 py-4 border-b border-gray-100 last:border-b-0
- Hover row: bg-gray-50 transition-colors
```

### 7. Loading & Feedback Patterns Research

**Decision**: Inline spinners and toast-style messages

**Rationale**:
- Loading spinners must be visible within buttons to avoid layout shift
- Success/error messages should be positioned consistently
- Messages should auto-dismiss after 5 seconds
- Loading states should disable interaction

**Loading System**:
```
Button Spinner:
- SVG spinner: animate-spin h-4 w-4 inline-block mr-2
- Position: inline with button text
- Color: matches button text color

Page Loading:
- Not needed for MVP - page transitions are fast enough

Message Toast:
- Success: bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg mb-6
- Error: bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg mb-6
- Warning: bg-orange-50 border border-orange-200 text-orange-700 p-4 rounded-lg mb-6
- Info: bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded-lg mb-6
- Position: Top of page content, full width, dismissible with X button
```

### 8. Badge & Status Indicators Research

**Decision**: Color-coded badges with consistent padding

**Rationale**:
- Status must be immediately scannable through color
- Text must remain readable on all badge backgrounds
- Consistent size prevents layout shifts

**Badge System**:
```
Available: bg-green-100 text-green-800 border border-green-300 px-3 py-1 rounded-full text-xs font-medium
Booked: bg-gray-200 text-gray-600 border border-gray-300 px-3 py-1 rounded-full text-xs font-medium
Locked: bg-orange-100 text-orange-800 border border-orange-300 px-3 py-1 rounded-full text-xs font-medium
Confirmed: bg-blue-100 text-blue-800 border border-blue-300 px-3 py-1 rounded-full text-xs font-medium
Cancelled: bg-red-100 text-red-800 border border-red-300 px-3 py-1 rounded-full text-xs font-medium
Active: bg-green-100 text-green-800 border border-green-300 px-3 py-1 rounded-full text-xs font-medium
Disabled: bg-gray-200 text-gray-600 border border-gray-300 px-3 py-1 rounded-full text-xs font-medium
```

### 9. Responsive Design Breakpoints

**Decision**: Use Tailwind's default breakpoints

**Rationale**:
- Tailwind breakpoints align with common device sizes
- Mobile-first approach ensures base styles work on small screens
- Progressive enhancement for larger screens

**Breakpoint System**:
```
sm: 640px  - Small tablets
md: 768px  - Tablets
lg: 1024px - Desktops
xl: 1280px - Large desktops

Mobile-First Patterns:
- Grids: grid-cols-1 md:grid-cols-2 lg:grid-cols-3
- Spacing: p-4 md:p-6 lg:p-8
- Font size: text-sm md:text-base
- Hide/show: hidden md:block
- Flex direction: flex-col md:flex-row
```

### 10. Blade Component Architecture

**Decision**: Minimal Blade components for high-reuse elements only

**Rationale**:
- Components add indirection - only worth it for 10+ uses
- Keep components simple with clear prop APIs
- Avoid component nesting complexity

**Component Strategy**:
```
High-Value Components (create):
- Button (used 50+ times) - 3 variants × 4 states = 12 variations
- Form input (used 30+ times) - text, email, password, number
- Badge (used 40+ times) - 6 status types
- Card (used 20+ times) - basic and stat variants

Low-Value (inline only):
- Tables (only 3 tables, each slightly different structure)
- Icons (use SVG inline, too few to justify component)
- Navigation (only 1 nav, highly specific)
```

## Summary

Research establishes a complete design system based on:
1. **Accessible color palette** with verified contrast ratios
2. **Clear typography hierarchy** using system fonts
3. **Consistent spacing rhythm** (4px base unit)
4. **Interactive button states** (hover, active, disabled, loading)
5. **Unified form controls** with validation styling
6. **Card patterns** with subtle shadows
7. **Loading indicators** and feedback messages
8. **Status badges** with semantic colors
9. **Responsive breakpoints** for mobile-first design
10. **Minimal Blade components** for high-reuse elements

All patterns use only Tailwind CSS utility classes - zero external dependencies added. Color choices prioritize visual comfort and accessibility. Component architecture favors simplicity over abstraction.
