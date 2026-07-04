<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Mata Kuliah</h2>

            <!-- Success Message (Green) -->
            @if(session('success'))
            <div class="mb-4 flex items-center bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm rounded-r" role="alert">
                <svg class="h-6 w-6 mr-2 flex-shrink-0" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            <!-- Error Message (Red) -->
            @if(session('error'))
            <div class="mb-4 flex items-center bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow-sm rounded-r" role="alert">
                <svg class="h-6 w-6 mr-2 flex-shrink-0" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    
                    <!-- Header Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
                        <!-- Filter Semester (Full width on mobile) -->
                        <form method="GET" action="{{ route('courses.index') }}" class="w-full sm:w-64">
                            <div class="relative">
                                <select name="semester" onchange="this.form.submit()" class="block w-full pl-4 pr-16 py-3 text-base border border-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md shadow-sm">
                                    <option value="">Semua Semester</option>
                                    @for($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </form>

                        <!-- Tambah MK Button (Full width on mobile) -->
                        <a href="{{ route('courses.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center justify-center w-full sm:w-auto">
                            + Tambah MK
                        </a>
                    </div>

                    <!-- Mobile Scroll Hint -->
                    <div class="lg:hidden mb-2 text-xs text-gray-500 italic flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                        Geser ke kanan untuk melihat semua kolom
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto -mx-4 sm:mx-0">
                        <div class="inline-block min-w-full align-middle">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-600">
                                            Kode MK
                                        </th>
                                        <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-600">
                                            Mata Kuliah
                                        </th>
                                        <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-center text-xs font-bold uppercase tracking-wider w-16 border-r border-gray-600">
                                            SKS
                                        </th>
                                        <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-600">
                                            Dosen Pengampu
                                        </th>
                                        <th scope="col" class="px-3 sm:px-6 py-2 sm:py-3 text-center text-xs font-bold uppercase tracking-wider w-24 sm:w-32">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($courses as $course)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 border-r border-gray-200 bg-gray-50">
                                            {{ $course->code }}
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-900 border-r border-gray-200 bg-gray-50">
                                            {{ $course->name }}
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-center text-gray-900 border-r border-gray-200 bg-gray-50">
                                            {{ $course->sks }}
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-gray-900 border-r border-gray-200 bg-gray-50">
                                            @php
                                                $lecturers = $course->users;
                                                $cpmkLecturers = $course->cpmks->pluck('lecturer')->filter()->unique('id');
                                                $allLecturers = $lecturers->merge($cpmkLecturers)->unique('id');
                                            @endphp
                                            
                                            @if($allLecturers->isNotEmpty())
                                                <ul class="list-none space-y-1">
                                                    @foreach($allLecturers as $lecturer)
                                                        <li class="truncate">{{ $lecturer->name }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-gray-400 italic">Belum ada dosen</span>
                                            @endif
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-center font-medium">
                                            <div class="flex justify-center space-x-2 sm:space-x-3">
                                                <a href="{{ route('courses.edit', $course) }}" class="text-gray-600 hover:text-gray-900" title="Edit">
                                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('courses.show', $course) }}" class="text-gray-600 hover:text-gray-900" title="Detail">
                                                    <!-- Info Icon -->
                                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </a>
                                                <form action="{{ route('courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $course->code }} - {{ $course->name }}? Data CPMK dan relasi lainnya juga akan terhapus.');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                                        <svg class="w-5 h-5 sm:w-6 sm:h-6" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            Belum ada data mata kuliah.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-end mt-4">
                        {{ $courses->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
