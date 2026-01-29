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

                    <!-- Phase 3 - Tasks T011-T016: Alpine.js Reactive Validation -->
                    <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm" 
                          x-data="bookingForm({{ $court->id }})" 
                          @submit.prevent="handleSubmit">
                        @csrf
                        <input type="hidden" name="court_id" value="{{ $court->id }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Date Selection -->
                            <div>
                                <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                                <select name="booking_date" id="booking_date" required 
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        x-model="selectedDate">
                                    <option value="">Choose a date</option>
                                    @foreach($availability as $date => $slots)
                                        <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('l, M j, Y') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Duration Selection - T014: Dynamic dropdown based on available durations -->
                            <div>
                                <label for="duration_hours" class="block text-sm font-medium text-gray-700 mb-2">Duration (hours)</label>
                                <select name="duration_hours" id="duration_hours" required 
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        x-model="selectedDuration"
                                        :disabled="!selectedTime || loadingDurations">
                                    <option value="">
                                        <span x-show="!selectedTime">Select a time slot first</span>
                                        <span x-show="selectedTime && loadingDurations">Loading options...</span>
                                        <span x-show="selectedTime && !loadingDurations">Select duration</span>
                                    </option>
                                    <!-- T014: Loop through availableDurations array -->
                                    <template x-for="duration in availableDurations" :key="duration">
                                        <option :value="duration" x-text="duration + (duration === 1 ? ' hour' : ' hours')"></option>
                                    </template>
                                </select>
                                <!-- Display reason if max duration is limited -->
                                <p x-show="maxDurationReason" class="mt-1 text-sm text-gray-600" x-text="maxDurationReason"></p>
                                <!-- T041: No available durations message -->
                                <p x-show="selectedTime && !loadingDurations && availableDurations.length === 0" 
                                   class="mt-1 text-sm text-red-600">
                                    No available durations for this time slot.
                                </p>
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
                                <span id="totalPrice" class="text-2xl font-bold text-blue-600" 
                                      x-text="'$' + (selectedDuration ? (selectedDuration * {{ $court->hourly_price }}).toFixed(2) : '0.00')">$0.00</span>
                            </div>
                        </div>

                        <!-- T016: Submit button disabled when form is invalid -->
                        <div x-show="!isValid() && selectedTime" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                <span x-show="!selectedDuration">Please select a duration to continue.</span>
                                <span x-show="selectedDuration && availableDurations.length === 0">Selected time slot is not available.</span>
                            </p>
                        </div>

                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                x-bind:disabled="!isValid()" 
                                x-bind:class="{ 'opacity-50 cursor-not-allowed': !isValid() }">
                            <span x-show="!loading">Proceed to Payment</span>
                            <span x-show="loading">Processing...</span>
                        </button>
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

        /**
         * Phase 3 - Tasks T011-T016: Alpine.js Reactive Booking Form Component
         * 
         * Purpose: Provides real-time validation and dynamic duration dropdown
         * Features:
         * - T011: Reactive data (selectedDate, selectedTime, selectedDuration, availableDurations)
         * - T012: fetchAvailableDurations() async method
         * - T013: Event-triggered API calls
         * - T015: isValid() computed property
         * - T016: Submit button disabled based on validation
         */
        function bookingForm(courtId) {
            return {
                // T011: Reactive state
                selectedDate: '',
                selectedTime: '',
                selectedDuration: '',
                availableDurations: [],
                maxDurationReason: null,
                loading: false,
                loadingDurations: false,

                // T012: Fetch available durations from API
                async fetchAvailableDurations() {
                    if (!this.selectedDate || !this.selectedTime) {
                        this.availableDurations = [];
                        this.maxDurationReason = null;
                        return;
                    }

                    this.loadingDurations = true;
                    this.selectedDuration = ''; // Reset duration when fetching new options

                    try {
                        const datetime = `${this.selectedDate} ${this.selectedTime}:00`;
                        const response = await fetch(
                            `/api/courts/${courtId}/availability/durations?datetime=${encodeURIComponent(datetime)}`
                        );

                        // T042: Handle AJAX errors gracefully
                        if (!response.ok) {
                            console.error('Failed to fetch durations:', response.statusText);
                            this.availableDurations = [];
                            this.maxDurationReason = 'Unable to check availability. Please try again.';
                            this.loadingDurations = false;
                            return;
                        }

                        const data = await response.json();
                        this.availableDurations = data.durations || [];
                        this.maxDurationReason = data.reason || null;

                        // Auto-select first available duration if only one option
                        if (this.availableDurations.length === 1) {
                            this.selectedDuration = this.availableDurations[0];
                        }
                    } catch (error) {
                        console.error('Error fetching durations:', error);
                        this.availableDurations = [];
                        this.maxDurationReason = 'Network error. Please check your connection.';
                    } finally {
                        this.loadingDurations = false;
                    }
                },

                // T015: Computed property to check if form is valid
                isValid() {
                    return this.selectedDate && 
                           this.selectedTime && 
                           this.selectedDuration && 
                           this.availableDurations.includes(parseInt(this.selectedDuration));
                },

                // Handle form submission
                handleSubmit(event) {
                    if (!this.isValid()) {
                        event.preventDefault();
                        alert('Please complete all fields with valid selections.');
                        return;
                    }

                    this.loading = true;
                    
                    // Add hidden input with start_datetime
                    const startDatetime = `${this.selectedDate} ${this.selectedTime}:00`;
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'start_datetime';
                    hiddenInput.value = startDatetime;
                    event.target.appendChild(hiddenInput);
                    
                    // Submit the form
                    event.target.submit();
                },

                // T013: Watch for time slot selection and trigger API call
                init() {
                    this.$watch('selectedTime', () => {
                        this.fetchAvailableDurations();
                    });
                }
            };
        }

        /**
         * Phase 1: Timezone Utility Functions
         * Purpose: Convert UTC datetime to user's browser local timezone
         * Used for: Displaying booking times, tooltips, and all time-related UI elements
         */

        /**
         * Format a UTC datetime string to user's local timezone
         * @param {string} utcDatetimeString - UTC datetime in format "YYYY-MM-DD HH:mm:ss"
         * @param {object} options - Intl.DateTimeFormat options
         * @returns {string} Formatted time in user's local timezone (e.g., "2:00 PM EST")
         */
        function formatLocalTime(utcDatetimeString, options = {}) {
            // Default options: 12-hour format with timezone abbreviation
            const defaultOptions = {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
                timeZoneName: 'short'
            };
            
            // Merge custom options with defaults
            const formatOptions = { ...defaultOptions, ...options };
            
            // Parse UTC datetime string to Date object
            const utcDate = new Date(utcDatetimeString.replace(' ', 'T') + 'Z');
            
            // Format using browser's timezone via Intl.DateTimeFormat
            return new Intl.DateTimeFormat('en-US', formatOptions).format(utcDate);
        }

        /**
         * Check if a given datetime slot is in the past
         * @param {string} slotDatetimeString - Datetime in format "YYYY-MM-DD HH:mm:ss" (UTC)
         * @returns {boolean} True if slot time has passed in user's local timezone
         */
        function isPastSlot(slotDatetimeString) {
            // Parse slot datetime (assuming UTC if no timezone specified)
            const slotDate = new Date(slotDatetimeString.replace(' ', 'T') + 'Z');
            
            // Get current time in user's timezone
            const now = new Date();
            
            // Compare timestamps (both in user's local timezone)
            return slotDate < now;
        }

        /**
         * Format a time range for tooltips (e.g., "2:00 PM - 6:00 PM EST")
         * @param {string} startDatetime - Start datetime in UTC
         * @param {string} endDatetime - End datetime in UTC
         * @returns {string} Formatted range with timezone shown once at the end
         */
        function formatTimeRange(startDatetime, endDatetime) {
            // Format start time without timezone
            const startTime = formatLocalTime(startDatetime, {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            // Format end time with timezone
            const endTime = formatLocalTime(endDatetime);
            
            return `${startTime} - ${endTime}`;
        }

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
                            data-time="${slot}"
                            onclick="selectTimeSlot('${slot}', this)">
                        ${displayTime}
                    </button>
                `;
            }).join('');

            timeSlotsContainer.innerHTML = `
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                    ${slotsHtml}
                </div>
            `;
        }

        // T013: Function to select time slot and update Alpine.js state
        function selectTimeSlot(time, button) {
            // Update visual selection
            document.querySelectorAll('.time-slot').forEach(b => b.classList.remove('border-blue-600', 'bg-blue-100'));
            button.classList.add('border-blue-600', 'bg-blue-100');
            
            // Update Alpine.js component state
            // Access the Alpine component through the form element
            const formEl = document.getElementById('bookingForm');
            if (formEl && formEl.__x) {
                // Update selectedTime which triggers fetchAvailableDurations via watcher
                formEl.__x.$data.selectedTime = time;
            }
            
            // Keep legacy selectedSlot for backward compatibility
            selectedSlot = `${dateSelect.value} ${time}:00`;
        }

        // Legacy updateTotalPrice kept for backward compatibility
        function updateTotalPrice() {
            const duration = parseInt(durationSelect.value) || 0;
            const total = hourlyPrice * duration;
            if (totalPriceElement) {
                totalPriceElement.textContent = '$' + total.toFixed(2);
            }
        }

        dateSelect.addEventListener('change', updateTimeSlots);

        /**
         * Phase 1 Task T002: Test timezone conversion with various UTC timestamps
         * Runs on page load to verify timezone utilities work correctly
         */
        (function testTimezoneConversion() {
            console.log('=== Phase 1 Timezone Utilities Test ===');
            
            // Test 1: Format a UTC datetime to local timezone
            const testDatetime1 = '2026-01-28 14:00:00'; // 2 PM UTC
            const formattedTime1 = formatLocalTime(testDatetime1);
            console.log(`Test 1 - UTC: ${testDatetime1} → Local: ${formattedTime1}`);
            
            // Test 2: Check if past slot detection works
            const pastDatetime = '2026-01-27 10:00:00'; // Yesterday
            const futureDateTime = '2026-02-15 15:00:00'; // Future date
            console.log(`Test 2 - Past slot (${pastDatetime}): ${isPastSlot(pastDatetime)}`);
            console.log(`Test 2 - Future slot (${futureDateTime}): ${isPastSlot(futureDateTime)}`);
            
            // Test 3: Format time range with timezone
            const startTime = '2026-01-28 14:00:00';
            const endTime = '2026-01-28 18:00:00';
            const rangeFormatted = formatTimeRange(startTime, endTime);
            console.log(`Test 3 - Range: ${rangeFormatted}`);
            
            // Test 4: Different time formats
            const morningTime = '2026-01-28 09:00:00'; // 9 AM UTC
            const eveningTime = '2026-01-28 21:30:00'; // 9:30 PM UTC
            console.log(`Test 4 - Morning: ${formatLocalTime(morningTime)}`);
            console.log(`Test 4 - Evening: ${formatLocalTime(eveningTime)}`);
            
            console.log('=== Timezone Test Complete ===');
            console.log('ℹ️ Times displayed in your browser timezone:', Intl.DateTimeFormat().resolvedOptions().timeZone);
        })();
    </script>
</x-app-layout>
