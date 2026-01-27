<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Court') }}
            </h2>
            <a href="{{ route('admin.courts.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Courts
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="list-disc list-inside text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.courts.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Court Name <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   required 
                                   maxlength="255"
                                   value="{{ old('name') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="photo_url" class="block text-sm font-medium text-gray-700 mb-2">Photo URL</label>
                            <input type="url" 
                                   name="photo_url" 
                                   id="photo_url" 
                                   maxlength="500"
                                   value="{{ old('photo_url') }}"
                                   placeholder="https://example.com/image.jpg"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="mb-4">
                            <label for="hourly_price" class="block text-sm font-medium text-gray-700 mb-2">Hourly Price <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" 
                                       name="hourly_price" 
                                       id="hourly_price" 
                                       required 
                                       min="0"
                                       max="9999.99"
                                       step="0.01"
                                       value="{{ old('hourly_price') }}"
                                       class="w-full pl-7 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="operating_hours_start" class="block text-sm font-medium text-gray-700 mb-2">Opening Time <span class="text-red-500">*</span></label>
                                <input type="time" 
                                       name="operating_hours_start" 
                                       id="operating_hours_start" 
                                       required
                                       value="{{ old('operating_hours_start', '08:00') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="operating_hours_end" class="block text-sm font-medium text-gray-700 mb-2">Closing Time <span class="text-red-500">*</span></label>
                                <input type="time" 
                                       name="operating_hours_end" 
                                       id="operating_hours_end" 
                                       required
                                       value="{{ old('operating_hours_end', '22:00') }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('admin.courts.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
                                Create Court
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
