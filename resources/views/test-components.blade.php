<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Design System Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto space-y-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Design System Components Test</h1>
        
        {{-- Button Component Tests --}}
        <section class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Buttons with Interactive States</h2>
            <div class="flex flex-wrap gap-4 mb-6">
                <x-button variant="primary">Primary Button</x-button>
                <x-button variant="secondary">Secondary Button</x-button>
                <x-button variant="danger">Danger Button</x-button>
                <x-button variant="link" href="#">Link Button</x-button>
                <x-button variant="primary" :disabled="true">Disabled Button</x-button>
                <x-button variant="primary" :loading="true">Loading Button</x-button>
            </div>
            
            <div class="p-4 bg-blue-50 rounded" x-data="{ formLoading: false }">
                <h3 class="font-semibold mb-3">Test Loading State with Alpine.js:</h3>
                <form @submit.prevent="formLoading = true; setTimeout(() => { formLoading = false; alert('Form submitted!') }, 2000)">
                    <x-button variant="primary" type="submit" x-bind:loading="formLoading">
                        Submit Test Form
                    </x-button>
                </form>
                <p class="text-sm text-gray-600 mt-2">Click to see loading spinner for 2 seconds</p>
            </div>
            
            <div class="mt-4 p-4 bg-green-50 rounded">
                <h3 class="font-semibold mb-2">✓ Hover Effects Verification:</h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>• Buttons scale to 1.05 on hover (transform hover:scale-105)</li>
                    <li>• Shadow increases from sm to md on hover</li>
                    <li>• Background color darkens on hover</li>
                    <li>• Link buttons show underline on hover</li>
                    <li>• Disabled buttons show not-allowed cursor</li>
                    <li>• Loading buttons show spinner and wait cursor</li>
                </ul>
            </div>
        </section>
        
        {{-- Form Components Tests --}}
        <section class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Form Components</h2>
            <div class="max-w-md space-y-4">
                <x-form-input 
                    name="test_name" 
                    label="Name" 
                    placeholder="Enter your name"
                    :required="true"
                />
                
                <x-form-input 
                    name="test_email" 
                    type="email"
                    label="Email Address" 
                    placeholder="email@example.com"
                    value="test@example.com"
                />
                
                <x-form-input 
                    name="test_error" 
                    label="Field with Error" 
                    value="invalid"
                    error="This field contains an error"
                />
                
                <x-form-input 
                    name="test_disabled" 
                    label="Disabled Field" 
                    value="Cannot edit"
                    :disabled="true"
                />
            </div>
        </section>
        
        {{-- Card Component Tests --}}
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Cards</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-card>
                    <h3 class="text-xl font-bold mb-2">Default Card</h3>
                    <p class="text-gray-600">This is a default card component.</p>
                </x-card>
                
                <x-card variant="stat" accent="blue">
                    <p class="text-sm text-gray-600">Total Courts</p>
                    <p class="text-3xl font-bold text-gray-800">12</p>
                </x-card>
                
                <x-card :hoverable="true">
                    <h3 class="text-xl font-bold mb-2">Hoverable Card</h3>
                    <p class="text-gray-600">Hover over me!</p>
                </x-card>
            </div>
        </section>
        
        {{-- Badge Component Tests --}}
        <section class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Badges</h2>
            <div class="flex flex-wrap gap-3">
                <x-badge status="available" />
                <x-badge status="booked" />
                <x-badge status="locked" />
                <x-badge status="confirmed" />
                <x-badge status="cancelled" />
                <x-badge status="active" />
                <x-badge status="disabled" />
            </div>
            <div class="flex flex-wrap gap-3 mt-4">
                <x-badge status="available" size="small">Small</x-badge>
                <x-badge status="confirmed" size="small">Small</x-badge>
            </div>
        </section>
        
        {{-- Loading Spinner Component Tests --}}
        <section class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Loading Spinners</h2>
            <div class="flex items-center gap-8">
                <div class="text-center">
                    <x-loading-spinner size="small" color="blue" />
                    <p class="text-xs mt-2">Small</p>
                </div>
                <div class="text-center">
                    <x-loading-spinner size="default" color="blue" />
                    <p class="text-xs mt-2">Default</p>
                </div>
                <div class="text-center">
                    <x-loading-spinner size="large" color="blue" />
                    <p class="text-xs mt-2">Large</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
