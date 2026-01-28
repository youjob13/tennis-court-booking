<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tennis Court Booking') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-3xl font-bold mb-6">Available Courts</h1>
                    
                    @if($courts->isEmpty())
                        <p class="text-gray-500">No courts available at this time.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($courts as $court)
                                <div class="bg-white border border-gray-200 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                                    @if($court->photo_url)
                                        <img src="{{ $court->photo_url }}" alt="{{ $court->name }}" class="w-full h-48 object-cover">
                                    @else
                                        <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                                            <span class="text-gray-500 text-lg">No Image</span>
                                        </div>
                                    @endif
                                    
                                    <div class="p-4">
                                        <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $court->name }}</h3>
                                        <p class="text-gray-600 text-sm mb-3">{{ Str::limit($court->description, 100) }}</p>
                                        
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="text-2xl font-bold text-green-600">${{ number_format($court->hourly_price, 2) }}</span>
                                            <span class="text-sm text-gray-500">per hour</span>
                                        </div>
                                        
                                        <!-- Time Slots Section -->
                                        <div class="border-t pt-4">
                                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Today's Availability</h4>
                                            
                                            <div class="grid grid-cols-4 gap-2 mb-3">
                                                @php
                                                    $allSlots = array_merge(
                                                        $court->available_slots_today ?? [],
                                                        $court->booked_slots_today ?? [],
                                                        $court->locked_slots_today ?? []
                                                    );
                                                    sort($allSlots);
                                                    $displaySlots = array_slice($allSlots, 0, 8); // Show first 8 slots
                                                @endphp
                                                
                                                @forelse($displaySlots as $slot)
                                                    @php
                                                        if (in_array($slot, $court->available_slots_today ?? [])) {
                                                            $bgColor = 'bg-green-100 text-green-800 border-green-300';
                                                            $status = 'available';
                                                        } elseif (in_array($slot, $court->booked_slots_today ?? [])) {
                                                            $bgColor = 'bg-gray-200 text-gray-600 border-gray-300';
                                                            $status = 'booked';
                                                        } else {
                                                            $bgColor = 'bg-yellow-100 text-yellow-800 border-yellow-300';
                                                            $status = 'locked';
                                                        }
                                                    @endphp
                                                    
                                                    <div class="text-center p-2 rounded border {{ $bgColor }} text-xs font-medium">
                                                        {{ \Carbon\Carbon::createFromFormat('H:i', $slot)->format('g A') }}
                                                    </div>
                                                @empty
                                                    <p class="col-span-4 text-sm text-gray-500">No slots available</p>
                                                @endforelse
                                            </div>
                                            
                                            <div class="flex gap-4 text-xs text-gray-600 mb-3">
                                                <div class="flex items-center gap-1">
                                                    <span class="w-3 h-3 bg-green-100 border border-green-300 rounded"></span>
                                                    <span>Available</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="w-3 h-3 bg-gray-200 border border-gray-300 rounded"></span>
                                                    <span>Booked</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="w-3 h-3 bg-yellow-100 border border-yellow-300 rounded"></span>
                                                    <span>Locked</span>
                                                </div>
                                            </div>
                                            
                                            @auth
                                                <a href="{{ route('courts.show', $court->id) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
                                                    Book Now
                                                </a>
                                            @else
                                                <a href="{{ route('login') }}" class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
                                                    Login to Book
                                                </a>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
