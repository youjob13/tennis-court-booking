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

@php
    // Validate name is provided
    if (empty($name)) {
        throw new InvalidArgumentException("Form input component requires 'name' prop");
    }
    
    // Validate type
    $allowedTypes = ['text', 'email', 'password', 'number', 'tel', 'url'];
    if (!in_array($type, $allowedTypes)) {
        throw new InvalidArgumentException("Invalid input type: {$type}. Allowed: " . implode(', ', $allowedTypes));
    }
    
    // Generate unique ID for input
    $inputId = $attributes->get('id', $name);
    
    // Base input classes
    $baseClasses = 'block w-full px-4 py-2 text-gray-700 border rounded-md transition-all focus:outline-none focus:ring-2';
    
    // State-dependent classes
    if ($disabled) {
        $stateClasses = 'bg-gray-100 text-gray-500 cursor-not-allowed border-gray-300';
    } elseif ($error) {
        $stateClasses = 'border-red-500 focus:ring-red-500 focus:border-red-500';
    } else {
        $stateClasses = 'border-gray-300 focus:ring-blue-500 focus:border-blue-500';
    }
    
    $inputClasses = trim("{$baseClasses} {$stateClasses}");
    
    // Get old value or use provided value
    $inputValue = old($name, $value);
@endphp

<div {{ $attributes->only('class') }}>
    @if($label)
        <x-form-label :for="$inputId" :required="$required">
            {{ $label }}
        </x-form-label>
    @endif
    
    <input 
        type="{{ $type }}" 
        name="{{ $name }}" 
        id="{{ $inputId }}"
        value="{{ $inputValue }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        class="{{ $inputClasses }}"
        {{ $attributes->except(['class', 'id']) }}
    />
    
    @if($error)
        <x-form-error :message="$error" />
    @endif
</div>
