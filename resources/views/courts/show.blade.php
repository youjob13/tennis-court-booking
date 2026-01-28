<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $court->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <x-button variant="link" href="{{ route('courts.index') }}">
                    ← Back to Courts
                </x-button>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    @if($court->photo_url)
                        <img src="{{ $court->photo_url }}" alt="{{ $court->name }}" class="w-full h-96 object-cover rounded-lg mb-6">
                    @endif

                    <div class="mb-6">
                        <h1 class="text-2xl font-semibold text-gray-800 mb-2">{{ $court->name }}</h1>
                        <p class="text-gray-600 text-lg">{{ $court->description }}</p>
                    </div>

                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 font-semibold">Hourly Rate:</span>
                            <span class="text-3xl font-bold text-green-600">${{ number_format($court->hourly_price, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Book This Court</h2>

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <ul class="list-disc list-inside text-red-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm" x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        <input type="hidden" name="court_id" value="{{ $court->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Date Selection -->
                            <div>
                                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                                <select name="booking_date" id="booking_date" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Choose a date</option>
                                    @foreach($availability as $date => $slots)
                                        <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('l, M j, Y') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Duration Selection -->
                            <div>
                                <label for="duration_hours" class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
                                <select name="duration_hours" id="duration_hours" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select duration</option>
                                    @for($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}">{{ $i }} {{ $i === 1 ? 'hour' : 'hours' }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Time Slot Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Select Start Time</label>
                            <div id="timeSlotsContainer">
                                <p class="text-gray-500">Please select a date first</p>
                            </div>
                        </div>

                        <!-- Price Calculation -->
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-gray-700">Total Price:</span>
                                <span id="totalPrice" class="text-2xl font-bold text-blue-600">$0.00</span>
                            </div>
                        </div>

                        <x-button variant="primary" type="submit" class="w-full" x-bind:loading="loading">
                            Proceed to Payment
                        </x-button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const availability = @json($availability);
        const hourlyPrice = {{ $court->hourly_price }};
        const dateSelect = document.getElementById('booking_date');
        const durationSelect = document.getElementById('duration_hours');
        const timeSlotsContainer = document.getElementById('timeSlotsContainer');
        const totalPriceElement = document.getElementById('totalPrice');
        const form = document.getElementById('bookingForm');
        
        let selectedSlot = null;

        function updateTimeSlots() {
            const selectedDate = dateSelect.value;
            
            if (!selectedDate) {
                timeSlotsContainer.innerHTML = '<p class="text-gray-500">Please select a date first</p>';
                return;
            }

            const slots = availability[selectedDate];
            if (!slots || !slots.available || slots.available.length === 0) {
                timeSlotsContainer.innerHTML = '<p class="text-gray-500">No available slots for this date</p>';
                return;
            }

            const slotsHtml = slots.available.map(slot => {
                const time = slot.split(':');
                const hour = parseInt(time[0]);
                const displayTime = (hour < 12 ? hour : (hour === 12 ? 12 : hour - 12)) + (hour < 12 ? ' AM' : ' PM');
                
                return `
                    <button type="button" 
                            class="time-slot p-3 border-2 border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors"
                            data-slot="${selectedDate} ${slot}:00">
                        ${displayTime}
                    </button>
                `;
            }).join('');

            timeSlotsContainer.innerHTML = `
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                    ${slotsHtml}
                </div>
            `;

            // Add click handlers to time slots
            document.querySelectorAll('.time-slot').forEach(button => {
                button.addEventListener('click', function() {
                    document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('border-blue-600', 'bg-blue-100'));
                    this.classList.add('border-blue-600', 'bg-blue-100');
                    selectedSlot = this.dataset.slot;
                    updateTotalPrice();
                });
            });
        }

        function updateTotalPrice() {
            const duration = parseInt(durationSelect.value) || 0;
            const total = hourlyPrice * duration;
            totalPriceElement.textContent = '$' + total.toFixed(2);
        }

        dateSelect.addEventListener('change', updateTimeSlots);
        durationSelect.addEventListener('change', updateTotalPrice);

        form.addEventListener('submit', function(e) {
            if (!selectedSlot) {
                e.preventDefault();
                alert('Please select a time slot');
                return;
            }
            
            // Add hidden input with start_datetime
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'start_datetime';
            hiddenInput.value = selectedSlot;
            form.appendChild(hiddenInput);
        });
    </script>
</x-app-layout>
