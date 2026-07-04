<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Kelola Sistem</h2>

            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-4 flex items-center bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm rounded-r" role="alert">
                <svg class="h-6 w-6 mr-2 flex-shrink-0" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        @method('PUT')

                        @foreach($settings as $setting)
                            <div class="mb-6">
                                <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $setting->label }}
                                </label>
                                
                                @if($setting->type === 'textarea')
                                    <textarea 
                                        name="{{ $setting->key }}" 
                                        id="{{ $setting->key }}" 
                                        rows="4" 
                                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600">{{ $setting->value }}</textarea>
                                @else
                                    <input 
                                        type="text" 
                                        name="{{ $setting->key }}" 
                                        id="{{ $setting->key }}" 
                                        value="{{ $setting->value }}"
                                        class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600">
                                @endif
                            </div>
                        @endforeach

                        <div class="flex justify-end pt-4 border-t border-gray-100">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow-sm transition-colors">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
