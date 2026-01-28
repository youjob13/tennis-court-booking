@props([
    'variant' => 'primary',    // primary|secondary|danger|link
    'type' => 'button',        // button|submit|reset
    'loading' => false,        // boolean
    'disabled' => false,       // boolean
    'href' => null,            // string (converts to <a> tag if provided)
])

@php
    // Validate variant
    $allowedVariants = ['primary', 'secondary', 'danger', 'link'];
    if (!in_array($variant, $allowedVariants)) {
        throw new InvalidArgumentException("Invalid button variant: {$variant}. Allowed: " . implode(', ', $allowedVariants));
    }
    
    // Validate type
    $allowedTypes = ['button', 'submit', 'reset'];
    if (!in_array($type, $allowedTypes)) {
        throw new InvalidArgumentException("Invalid button type: {$type}. Allowed: " . implode(', ', $allowedTypes));
    }
    
    // Base classes for all buttons
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-md transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    // Variant-specific classes
    $variantClasses = match($variant) {
        'primary' => 'px-4 py-2 bg-blue-500 text-white text-sm hover:bg-blue-600 active:bg-blue-700 focus:ring-blue-500 shadow-sm hover:shadow-md transform hover:scale-105',
        'secondary' => 'px-4 py-2 bg-gray-200 text-gray-700 text-sm hover:bg-gray-300 active:bg-gray-400 focus:ring-gray-400 shadow-sm hover:shadow-md transform hover:scale-105',
        'danger' => 'px-4 py-2 bg-red-500 text-white text-sm hover:bg-red-600 active:bg-red-700 focus:ring-red-500 shadow-sm hover:shadow-md transform hover:scale-105',
        'link' => 'px-3 py-1.5 text-blue-500 text-sm hover:text-blue-600 hover:underline focus:ring-blue-500',
    };
    
    // Disabled state - unified gray styling
    $isDisabled = $disabled || $loading;
    if ($isDisabled && $variant !== 'link') {
        $variantClasses = 'px-4 py-2 bg-gray-300 text-gray-500 text-sm cursor-not-allowed opacity-50';
    }
    
    // Loading state
    if ($loading) {
        $variantClasses .= ' cursor-wait opacity-75';
    }
    
    $classes = trim("{$baseClasses} {$variantClasses}");
@endphp

@if($href && !$isDisabled)
    {{-- Render as link --}}
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    {{-- Render as button --}}
    <button 
        type="{{ $type }}" 
        :disabled="typeof loading !== 'undefined' ? loading : {{ $isDisabled ? 'true' : 'false' }}"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        <template x-if="typeof loading !== 'undefined' && loading">
            <x-loading-spinner size="small" color="white" class="mr-2" />
        </template>
        @if($loading)
            <x-loading-spinner size="small" color="white" class="mr-2" />
        @endif
        {{ $slot }}
    </button>
@endif
