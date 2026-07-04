<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Tambah Mata Kuliah</h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    
                    <div class="border-b-2 border-green-500 mb-6">
                        <h3 class="text-xl font-bold text-gray-800 pb-2">Detail Mata Kuliah</h3>
                    </div>

                    <form method="POST" action="{{ route('courses.store') }}">
                        @csrf

                        <!-- Kode MK -->
                        <div class="mb-6">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-3">
                                Kode MK <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="code" 
                                id="code" 
                                required
                                placeholder="Contoh: TIF101"
                                autocomplete="off"
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('code') border-red-500 @enderror" 
                                value="{{ old('code') }}">
                            @error('code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Mata Kuliah -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-3">
                                Nama Mata Kuliah <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name"
                                required
                                placeholder="Contoh: Algoritma dan Pemrograman"
                                autocomplete="off"
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('name') border-red-500 @enderror" 
                                value="{{ old('name') }}">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKS -->
                        <div class="mb-6">
                            <label for="sks" class="block text-sm font-medium text-gray-700 mb-3">
                                SKS <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="sks" 
                                id="sks" 
                                min="1"
                                required
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('sks') border-red-500 @enderror" 
                                value="{{ old('sks') }}">
                            @error('sks')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Semester -->
                        <div class="mb-6">
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-3">
                                Semester <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="semester" 
                                id="semester"
                                required 
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('semester') border-red-500 @enderror">
                                <option value="" disabled selected>Pilih Semester</option>
                                @for($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}" {{ old('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                                @endfor
                            </select>
                            @error('semester')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mata Kuliah Prasyarat -->
                        <div class="mb-6">
                            <label for="prerequisite_course_id" class="block text-sm font-medium text-gray-700 mb-3">
                                Mata Kuliah Prasyarat
                            </label>
                            <select 
                                name="prerequisite_course_id" 
                                id="prerequisite_course_id" 
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('prerequisite_course_id') border-red-500 @enderror">
                                <option value="">Tidak Ada</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('prerequisite_course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->code }} - {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prerequisite_course_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CPL yang Didukung -->
                        <div class="mb-6">
                            <label for="cpl_ids" class="block text-sm font-medium text-gray-700 mb-3">
                                CPL yang Didukung <span class="text-red-500">*</span>
                            </label>
                            <div class="border border-gray-300 rounded-md p-3 bg-white" style="height: 280px; overflow-y: scroll; overscroll-behavior: contain;">
                                @foreach($cpls as $cpl)
                                    <label class="flex items-start gap-3 p-3 hover:bg-gray-50 rounded cursor-pointer mb-2 last:mb-0 border-b border-gray-100 last:border-0">
                                        <input 
                                            type="checkbox" 
                                            name="cpl_ids[]" 
                                            value="{{ $cpl->id }}" 
                                            {{ (collect(old('cpl_ids'))->contains($cpl->id)) ? 'checked' : '' }}
                                            class="mt-1 h-4 w-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                        <div class="flex-1 min-w-0">
                                            <span class="block text-sm font-medium text-gray-800">{{ $cpl->code }}</span>
                                            <span class="block text-xs text-gray-600 leading-relaxed mt-1">{{ $cpl->description }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Pilih minimal satu CPL (scroll untuk melihat lebih banyak)</p>
                            @error('cpl_ids')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded text-sm">
                                Simpan dan Lanjutkan
                            </button>
                            <a href="{{ route('courses.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-6 rounded text-sm inline-flex items-center">
                                Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
