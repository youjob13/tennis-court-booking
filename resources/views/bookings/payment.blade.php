<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Complete Payment
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Lock Expiration Warning -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <span class="text-yellow-800 font-semibold">⏰ Your booking is held for:</span>
                    <span id="countdown" class="text-2xl font-bold text-yellow-600"></span>
                </div>
                <p class="text-yellow-700 text-sm mt-2">Complete payment before time expires or the booking will be released.</p>
            </div>

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="list-disc list-inside text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Booking Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Booking Summary</h3>
                    
                    <div class="space-y-3">
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
                            <span class="text-xl font-bold text-gray-800">Total Amount:</span>
                            <span class="text-2xl font-bold text-green-600">${{ number_format($booking->total_price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Payment Information</h3>

                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800">
                        <strong>Test Payment:</strong> This is a dummy payment system for demonstration.
                        <br>
                        Use any card number EXCEPT those ending in '0000' (which will simulate a payment failure).
                    </div>

                    <form action="{{ route('bookings.payment.process', $booking) }}" method="POST" id="paymentForm">
                        @csrf

                        <div class="mb-4">
                            <label for="card_number" class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
                            <input type="text" 
                                   name="card_number" 
                                   id="card_number" 
                                   required 
                                   maxlength="16"
                                   pattern="\d{16}"
                                   placeholder="1234567812345678"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="card_expiry" class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                                <input type="text" 
                                       name="card_expiry" 
                                       id="card_expiry" 
                                       required 
                                       maxlength="5"
                                       pattern="\d{2}/\d{2}"
                                       placeholder="MM/YY"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="card_cvv" class="block text-sm font-medium text-gray-700 mb-2">CVV</label>
                                <input type="text" 
                                       name="card_cvv" 
                                       id="card_cvv" 
                                       required 
                                       maxlength="3"
                                       pattern="\d{3}"
                                       placeholder="123"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <button type="submit" 
                                id="submitBtn"
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200">
                            Complete Payment - ${{ number_format($booking->total_price, 2) }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown Timer
        const lockExpiresAt = new Date('{{ $booking->lock_expires_at }}').getTime();
        const countdownElement = document.getElementById('countdown');
        const submitBtn = document.getElementById('submitBtn');

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = lockExpiresAt - now;

            if (distance < 0) {
                clearInterval(timer);
                countdownElement.innerHTML = 'EXPIRED';
                countdownElement.classList.add('text-red-600');
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                
                // Redirect to courts page after 2 seconds
                setTimeout(() => {
                    window.location.href = '{{ route("courts.index") }}';
                }, 2000);
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElement.innerHTML = `${minutes}m ${seconds}s`;

            if (minutes < 2) {
                countdownElement.classList.add('text-red-600');
            }
        }

        updateCountdown();
        const timer = setInterval(updateCountdown, 1000);

        // Card number formatting
        document.getElementById('card_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substr(0, 16);
        });

        // Expiry formatting
        document.getElementById('card_expiry').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substr(0, 2) + '/' + value.substr(2, 2);
            }
            this.value = value;
        });

        // CVV formatting
        document.getElementById('card_cvv').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').substr(0, 3);
        });
    </script>
</x-app-layout>
