@props([
    'variant' => 'default',    // default|stat|table
    'accent' => null,          // blue|green|red|orange|gray (for stat variant only)
    'hoverable' => false,      // boolean
])

@php
    // Validate variant
    $allowedVariants = ['default', 'stat', 'table'];
    if (!in_array($variant, $allowedVariants)) {
        throw new InvalidArgumentException("Invalid card variant: {$variant}. Allowed: " . implode(', ', $allowedVariants));
    }
    
    // Validate accent only used with stat variant
    if ($accent && $variant !== 'stat') {
        throw new InvalidArgumentException("Card 'accent' prop can only be used with variant='stat'");
    }
    
    // Validate accent values
    if ($accent) {
        $allowedAccents = ['blue', 'green', 'red', 'orange', 'gray'];
        if (!in_array($accent, $allowedAccents)) {
            throw new InvalidArgumentException("Invalid card accent: {$accent}. Allowed: " . implode(', ', $allowedAccents));
        }
    }
    
    // Base classes for all cards
    $baseClasses = 'bg-white rounded-lg';
    
    // Variant-specific classes
    $variantClasses = match($variant) {
        'default' => 'shadow-md p-6 border border-gray-100',
        'stat' => 'shadow-md p-6 border border-gray-100 border-l-4 border-l-' . ($accent ?? 'blue') . '-500',
        'table' => 'shadow-sm overflow-hidden',
    };
    
    // Hoverable classes
    $hoverClasses = $hoverable ? 'hover:shadow-lg hover:scale-102 transition-all duration-200 cursor-pointer' : '';
    
    $classes = trim("{$baseClasses} {$variantClasses} {$hoverClasses}");
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
