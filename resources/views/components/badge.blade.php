@props([
    'status' => 'available',   // available|booked|locked|confirmed|cancelled|active|disabled
    'size' => 'default',       // small|default
])

@php
    // Validate status
    $allowedStatuses = ['available', 'booked', 'locked', 'confirmed', 'cancelled', 'active', 'disabled'];
    if (!in_array($status, $allowedStatuses)) {
        throw new InvalidArgumentException("Invalid badge status: {$status}. Allowed: " . implode(', ', $allowedStatuses));
    }
    
    // Validate size
    $allowedSizes = ['small', 'default'];
    if (!in_array($size, $allowedSizes)) {
        throw new InvalidArgumentException("Invalid badge size: {$size}. Allowed: " . implode(', ', $allowedSizes));
    }
    
    // Base classes
    $baseClasses = 'inline-flex items-center rounded-full font-medium border';
    
    // Size classes
    $sizeClasses = match($size) {
        'small' => 'px-2 py-0.5 text-xs',
        'default' => 'px-3 py-1 text-xs',
    };
    
    // Status color classes
    $statusClasses = match($status) {
        'available' => 'bg-green-100 text-green-800 border-green-300',
        'active' => 'bg-green-100 text-green-800 border-green-300',
        'booked' => 'bg-gray-200 text-gray-600 border-gray-300',
        'disabled' => 'bg-gray-200 text-gray-600 border-gray-300',
        'locked' => 'bg-orange-100 text-orange-800 border-orange-300',
        'confirmed' => 'bg-blue-100 text-blue-800 border-blue-300',
        'cancelled' => 'bg-red-100 text-red-800 border-red-300',
    };
    
    $classes = trim("{$baseClasses} {$sizeClasses} {$statusClasses}");
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot ?: ucfirst($status) }}
</span>
