<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah CPMK</h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    
                    <div class="border-b-2 border-green-500 mb-6">
                        <h3 class="text-xl font-bold text-gray-800 pb-2">
                            Detail CPMK untuk: <span class="text-green-600">{{ $course->code }} - {{ $course->name }}</span>
                        </h3>
                    </div>

                    <form method="POST" action="{{ route('cpmks.store') }}">
                        @csrf
                        <input type="hidden" name="course_id" value="{{ $course->id }}">

                        <div class="space-y-8 mb-6">
                            
                            <!-- Kode CPMK -->
                            <div>
                                <label for="code" class="block text-sm font-bold text-gray-700 mb-3">
                                    Kode CPMK <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="code" 
                                    id="code" 
                                    required
                                    class="block w-full md:w-1/2 rounded-md border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" 
                                    placeholder="Contoh: CPMK 01" 
                                    value="{{ old('code') }}">
                                @error('code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pernyataan CPMK -->
                            <div>
                                <label for="description" class="block text-sm font-bold text-gray-700 mb-3">
                                    Pernyataan CPMK <span class="text-red-500">*</span>
                                </label>
                                <textarea 
                                    name="description" 
                                    id="description" 
                                    rows="5" 
                                    required
                                    class="block w-full rounded-md border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Dosen Pengampu -->
                            <div>
                                <label for="lecturer_id" class="block text-sm font-bold text-gray-700 mb-3">
                                    Dosen Pengampu <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    name="lecturer_id" 
                                    id="lecturer_id"
                                    class="block w-full rounded-md border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50">
                                    <option value="">Pilih Dosen Pengampu</option>
                                    @foreach($lecturers as $lecturer)
                                        <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                                            {{ $lecturer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lecturer_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Indikator -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-3">
                                    Indikator <span class="text-red-500">*</span>
                                </label>
                                <div class="space-y-4">
                                    <input 
                                        type="text" 
                                        name="indicators[]" 
                                        placeholder="Indikator 1"
                                        class="block w-full rounded-md border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" 
                                        value="{{ old('indicators.0') }}">
                                    
                                    <input 
                                        type="text" 
                                        name="indicators[]" 
                                        placeholder="Indikator 2"
                                        class="block w-full rounded-md border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" 
                                        value="{{ old('indicators.1') }}">
                                    
                                    <input 
                                        type="text" 
                                        name="indicators[]" 
                                        placeholder="Indikator 3"
                                        class="block w-full rounded-md border-gray-300 px-4 py-2.5 shadow-sm focus:border-green-500 focus:ring focus:ring-green-500 focus:ring-opacity-50" 
                                        value="{{ old('indicators.2') }}">
                                </div>
                                @error('indicators')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('courses.show', $course) }}" 
                               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-md shadow-sm">
                                Batal
                            </a>
                            <button 
                                type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-md shadow-sm">
                                Simpan
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
