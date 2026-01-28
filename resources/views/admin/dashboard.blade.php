<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800 mb-8">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Courts -->
                <x-card variant="stat" accent="blue">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Total Courts</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $stats['total_courts'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $stats['active_courts'] }} active, {{ $stats['disabled_courts'] }} disabled
                            </p>
                        </div>
                        <div class="text-blue-500">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                </x-card>

                <!-- Total Bookings -->
                <x-card variant="stat" accent="green">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Total Bookings</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $stats['total_bookings'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $stats['confirmed_bookings'] }} confirmed
                            </p>
                        </div>
                        <div class="text-green-500">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                </x-card>

                <!-- Locked Bookings -->
                <x-card variant="stat" accent="orange">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Locked Bookings</p>
                            <p class="text-3xl font-bold text-gray-800">{{ $stats['locked_bookings'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Awaiting payment
                            </p>
                        </div>
                        <div class="text-orange-500">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                    </div>
                </x-card>

                <!-- Total Revenue -->
                <x-card variant="stat" accent="red">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-sm text-gray-600">Total Revenue</p>
                            <p class="text-3xl font-bold text-gray-800">${{ number_format($stats['total_revenue'], 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Today: ${{ number_format($stats['today_revenue'], 2) }}
                            </p>
                        </div>
                        <div class="text-red-500">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="{{ route('admin.courts.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Manage Courts</h3>
                        <p class="text-gray-600 text-sm">Add, edit, or disable tennis courts</p>
                    </div>
                </a>

                <a href="{{ route('admin.bookings.index') }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">Manage Bookings</h3>
                        <p class="text-gray-600 text-sm">View and cancel locked bookings</p>
                    </div>
                </a>

                <a href="{{ route('admin.courts.create') }}" class="block bg-blue-600 overflow-hidden shadow-sm sm:rounded-lg hover:bg-blue-700 transition-colors">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-white mb-2">+ Add New Court</h3>
                        <p class="text-blue-100 text-sm">Create a new tennis court</p>
                    </div>
                </a>
            </div>

            <!-- Recent Bookings -->
            <x-card variant="table">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Bookings</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">ID</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Court</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">User</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Date/Time</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Duration</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Price</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($recent_bookings as $booking)
                            <tr class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-800">#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ $booking->court->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ $booking->user->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ \Carbon\Carbon::parse($booking->start_datetime)->format('M j, Y g:i A') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ $booking->duration_hours }}h</td>
                                <td class="px-6 py-4 text-sm text-gray-800">${{ number_format($booking->total_price, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    <x-badge :status="$booking->status === 'confirmed' ? 'confirmed' : ($booking->status === 'locked' ? 'locked' : 'cancelled')" />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No bookings yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
