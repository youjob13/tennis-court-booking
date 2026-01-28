@props([
    'for' => null,             // string (input id)
    'required' => false,       // boolean
])

<label 
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-sm font-semibold text-gray-700 mb-2']) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-red-500 ml-1">*</span>
    @endif
</label>
