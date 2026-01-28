<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800 mb-8">
                {{ __('Manage Courts') }}
            </h2>
            <x-button variant="primary" href="{{ route('admin.courts.create') }}">
                + Add New Court
            </x-button>
        </div>
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
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Name</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Price/Hour</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Operating Hours</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Status</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-left">Future Bookings</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-700 uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse($courts as $court)
                            <tr class="border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        @if($court->photo_url)
                                            <img src="{{ $court->photo_url }}" alt="{{ $court->name }}" class="w-10 h-10 rounded object-cover mr-3">
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-800">{{ $court->name }}</div>
                                            <div class="text-sm text-gray-500">{{ Str::limit($court->description, 40) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    ${{ number_format($court->hourly_price, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ $court->operating_hours['start'] }} - {{ $court->operating_hours['end'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    <x-badge :status="$court->status === 'active' ? 'active' : 'disabled'" />
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    {{ $court->bookings_count }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            @if($court->status === 'active')
                                                <form action="{{ route('admin.courts.disable', $court) }}" method="POST" class="inline" x-data="{ loading: false }" @submit="loading = true">
                                                    @csrf
                                                    @method('PATCH')
                                                    <x-button variant="secondary" type="submit" x-bind:loading="loading">Disable</x-button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.courts.enable', $court) }}" method="POST" class="inline" x-data="{ loading: false }" @submit="loading = true">
                                                    @csrf
                                                    @method('PATCH')
                                                    <x-button variant="secondary" type="submit" x-bind:loading="loading">Enable</x-button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('admin.courts.destroy', $court) }}" method="POST" class="inline" x-data="{ loading: false }" @submit="loading = true"
                                                  onsubmit="return confirm('Are you sure you want to delete this court?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-button variant="danger" type="submit" x-bind:loading="loading">Delete</x-button>
                                            </form>
                                        </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No courts found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
