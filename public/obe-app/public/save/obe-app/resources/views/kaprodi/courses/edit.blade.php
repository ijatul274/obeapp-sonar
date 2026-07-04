<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Mata Kuliah</h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6">
                    
                    <div class="border-b-2 border-green-500 mb-6">
                        <h3 class="text-xl font-bold text-gray-800 pb-2">Detail Mata Kuliah</h3>
                    </div>

                    <form method="POST" action="{{ route('courses.update', $course) }}">
                        @csrf
                        @method('PUT')

                        <!-- Kode MK -->
                        <div class="mb-10">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-3">
                                Kode MK <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="code" 
                                id="code" 
                                required
                                autocomplete="off"
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('code') border-red-500 @enderror" 
                                value="{{ old('code', $course->code) }}">
                            @error('code')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Mata Kuliah -->
                        <div class="mb-10">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-3">
                                Nama Mata Kuliah <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="name" 
                                id="name"
                                required
                                autocomplete="off"
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('name') border-red-500 @enderror" 
                                value="{{ old('name', $course->name) }}">
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- SKS -->
                        <div class="mb-10">
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
                                value="{{ old('sks', $course->sks) }}">
                            @error('sks')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Semester -->
                        <div class="mb-10">
                            <label for="semester" class="block text-sm font-medium text-gray-700 mb-3">
                                Semester <span class="text-red-500">*</span>
                            </label>
                            <select 
                                name="semester" 
                                id="semester"
                                required 
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('semester') border-red-500 @enderror">
                                <option value="" disabled>Pilih Semester</option>
                                @for($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}" {{ old('semester', $course->semester) == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                                @endfor
                            </select>
                            @error('semester')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Mata Kuliah Prasyarat -->
                        <div class="mb-10">
                            <label for="prerequisite_course_id" class="block text-sm font-medium text-gray-700 mb-3">
                                Mata Kuliah Prasyarat
                            </label>
                            <select 
                                name="prerequisite_course_id" 
                                id="prerequisite_course_id" 
                                class="w-full rounded-md border border-gray-300 px-4 py-3 text-sm text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600 @error('prerequisite_course_id') border-red-500 @enderror">
                                <option value="">Tidak Ada</option>
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}" {{ old('prerequisite_course_id', $course->prerequisite_course_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->code }} - {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('prerequisite_course_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CPL yang Didukung -->
                        <div class="mb-10">
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
                                            {{ (collect(old('cpl_ids', $course->cpls->pluck('id')))->contains($cpl->id)) ? 'checked' : '' }}
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



                        <!-- Manajemen CPMK -->
                        <div class="mb-10 border-t border-gray-200 pt-8">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Daftar CPMK</h3>
                                    <p class="text-sm text-gray-500">Kelola Capaian Pembelajaran Mata Kuliah di sini.</p>
                                </div>
                                <a href="{{ route('cpmks.create', $course) }}" class="bg-white border border-green-600 text-green-600 hover:bg-green-50 font-bold py-2 px-4 rounded text-sm inline-flex items-center shadow-sm transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah CPMK
                                </a>
                            </div>

                            <div class="bg-white rounded-md border border-gray-200 divide-y divide-gray-100 shadow-sm">
                                @forelse($course->cpmks as $cpmk)
                                    <div class="p-3 flex items-start justify-between gap-3 hover:bg-gray-50 transition-colors">
                                        <!-- Info CPMK -->
                                        <div class="flex gap-3">
                                            <div class="flex-shrink-0 mt-0.5">
                                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded border border-green-200">
                                                    {{ $cpmk->code }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-gray-900 font-medium text-sm leading-snug">
                                                    {{ $cpmk->description }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-1.5">
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                    <span class="text-xs text-gray-500 font-medium">
                                                        {{ $cpmk->lecturer ? $cpmk->lecturer->name : 'Belum ditentukan' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                            <a href="{{ route('cpmks.edit', $cpmk) }}" class="text-gray-600 hover:text-blue-600 p-1.5 hover:bg-blue-50 rounded-md transition-colors" title="Edit CPMK">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </a>
                                            
                                            <button type="button" onclick="if(confirm('Hapus CPMK ini?')) document.getElementById('delete-cpmk-{{ $cpmk->id }}').submit()" class="text-gray-600 hover:text-red-600 p-1.5 hover:bg-red-50 rounded-md transition-colors" title="Hapus CPMK">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-8">
                                        <p class="text-sm text-gray-500 mb-2">Belum ada CPMK yang ditambahkan.</p>
                                    </div>
                                @endforelse
                            </div>
                            

                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-3">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-6 rounded text-sm">
                                Perbarui
                            </button>
                            <a href="{{ route('courses.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-6 rounded text-sm inline-flex items-center">
                                Batal
                            </a>
                        </div>

                    </form>

                    <!-- Hidden Forms for Delete (Moved outside main form) -->
                    @foreach($course->cpmks as $cpmk)
                        <form id="delete-cpmk-{{ $cpmk->id }}" action="{{ route('cpmks.destroy', $cpmk) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
