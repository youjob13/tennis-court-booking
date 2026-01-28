@props([
    'size' => 'default',       // small|default|large
    'color' => 'current',      // current|white|blue
])

@php
    // Validate size
    $allowedSizes = ['small', 'default', 'large'];
    if (!in_array($size, $allowedSizes)) {
        throw new InvalidArgumentException("Invalid spinner size: {$size}. Allowed: " . implode(', ', $allowedSizes));
    }
    
    // Validate color
    $allowedColors = ['current', 'white', 'blue'];
    if (!in_array($color, $allowedColors)) {
        throw new InvalidArgumentException("Invalid spinner color: {$color}. Allowed: " . implode(', ', $allowedColors));
    }
    
    // Size classes
    $sizeClasses = match($size) {
        'small' => 'w-4 h-4',
        'default' => 'w-6 h-6',
        'large' => 'w-12 h-12',
    };
    
    // Color classes
    $colorClasses = match($color) {
        'current' => 'text-current',
        'white' => 'text-white',
        'blue' => 'text-blue-500',
    };
    
    $classes = trim("animate-spin {$sizeClasses} {$colorClasses}");
@endphp

<svg 
    {{ $attributes->merge(['class' => $classes]) }}
    xmlns="http://www.w3.org/2000/svg" 
    fill="none" 
    viewBox="0 0 24 24"
>
    <circle 
        class="opacity-25" 
        cx="12" 
        cy="12" 
        r="10" 
        stroke="currentColor" 
        stroke-width="4"
    ></circle>
    <path 
        class="opacity-75" 
        fill="currentColor" 
        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
    ></path>
</svg>
