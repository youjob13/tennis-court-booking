<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800 mb-8">
            {{ __('Manage Bookings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="list-disc list-inside text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-card variant="table">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Locked & Future Bookings</h3>
                    <p class="text-sm text-gray-600">Only locked bookings can be cancelled. Confirmed bookings cannot be cancelled from admin panel.</p>
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
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Lock Expires</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($bookings as $booking)
                            <tr class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ $booking->court->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-800">{{ $booking->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $booking->user->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ \Carbon\Carbon::parse($booking->start_datetime)->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ $booking->duration_hours }}h
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    ${{ number_format($booking->total_price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    <x-badge :status="$booking->status === 'confirmed' ? 'confirmed' : ($booking->status === 'locked' ? 'locked' : 'cancelled')" />
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    @if($booking->lock_expires_at)
                                        {{ \Carbon\Carbon::parse($booking->lock_expires_at)->diffForHumans() }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                        @if($booking->status === 'locked')
                                            <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="inline" x-data="{ loading: false }" @submit="loading = true"
                                                  onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-button variant="danger" type="submit" x-bind:loading="loading">Cancel</x-button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">Protected</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">No bookings found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $bookings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
