<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Booking Confirmed
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Success Message -->
            <div class="mb-6 p-6 bg-green-50 border border-green-200 rounded-lg text-center">
                <div class="text-6xl mb-4">✓</div>
                <h2 class="text-3xl font-bold text-green-800 mb-2">Booking Confirmed!</h2>
                <p class="text-green-700">Your tennis court has been successfully booked.</p>
            </div>

            <!-- Booking Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Booking Details</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Booking ID:</span>
                            <span class="font-mono font-semibold text-gray-800">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Court:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->court->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Date & Time:</span>
                            <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($booking->start_datetime)->format('l, M j, Y - g:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Duration:</span>
                            <span class="font-semibold text-gray-800">{{ $booking->duration_hours }} {{ $booking->duration_hours === 1 ? 'hour' : 'hours' }}</span>
                        </div>
                        <div class="border-t pt-3 flex justify-between">
                            <span class="text-gray-600">Payment Reference:</span>
                            <span class="font-mono text-sm text-gray-800">{{ $booking->payment_reference }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-xl font-bold text-gray-800">Total Paid:</span>
                            <span class="text-2xl font-bold text-green-600">${{ number_format($booking->total_price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h4 class="font-bold text-blue-800 mb-2">📋 Important Information</h4>
                <ul class="text-blue-700 text-sm space-y-1 list-disc list-inside">
                    <li>Please arrive at least 10 minutes before your booking time</li>
                    <li>Bring your booking ID or payment reference for check-in</li>
                    <li>Court equipment is available at the front desk</li>
                    <li>Check your email for a confirmation receipt</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ route('courts.index') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-center transition-colors duration-200">
                    Book Another Court
                </a>
                <button onclick="window.print()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-lg text-center transition-colors duration-200">
                    Print Confirmation
                </button>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .hidden-print, header, nav, button {
                display: none !important;
            }
        }
    </style>
</x-app-layout>
