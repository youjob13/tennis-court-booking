<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Add New Court') }}
            </h2>
            <x-button variant="link" href="{{ route('admin.courts.index') }}">
                ← Back to Courts
            </x-button>
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
                    <form action="{{ route('admin.courts.store') }}" method="POST" x-data="{ loading: false }" @submit="loading = true">
                        @csrf

                        <x-form-input 
                            name="name" 
                            type="text" 
                            label="Court Name" 
                            :required="true"
                            :value="old('name')"
                            :error="$errors->first('name')"
                            maxlength="255"
                            class="mb-4"
                        />

                        <div class="mb-4">
                            <x-form-label for="description" label="Description" />
                            <textarea name="description" 
                                      id="description" 
                                      rows="3"
                                      class="block w-full px-4 py-2 border border-gray-300 rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">{{ old('description') }}</textarea>
                            @if($errors->first('description'))
                                <x-form-error :message="$errors->first('description')" />
                            @endif
                        </div>

                        <x-form-input 
                            name="photo_url" 
                            type="url" 
                            label="Photo URL" 
                            :value="old('photo_url')"
                            :error="$errors->first('photo_url')"
                            placeholder="https://example.com/image.jpg"
                            maxlength="500"
                            class="mb-4"
                        />

                        <div class="mb-4">
                            <x-form-label for="hourly_price" :required="true">Hourly Price</x-form-label>
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
                                       class="block w-full pl-7 px-4 py-2 border border-gray-300 rounded-md text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            </div>
                            @if($errors->first('hourly_price'))
                                <x-form-error :message="$errors->first('hourly_price')" />
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <x-form-input 
                                name="operating_hours_start" 
                                type="time" 
                                label="Opening Time" 
                                :required="true"
                                :value="old('operating_hours_start', '08:00')"
                                :error="$errors->first('operating_hours_start')"
                            />

                            <x-form-input 
                                name="operating_hours_end" 
                                type="time" 
                                label="Closing Time" 
                                :required="true"
                                :value="old('operating_hours_end', '22:00')"
                                :error="$errors->first('operating_hours_end')"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <x-button variant="link" href="{{ route('admin.courts.index') }}">
                                Cancel
                            </x-button>
                            <x-button variant="primary" type="submit" x-bind:loading="loading">
                                Create Court
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
