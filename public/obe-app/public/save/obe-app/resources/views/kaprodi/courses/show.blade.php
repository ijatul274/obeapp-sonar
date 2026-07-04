<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Detail Mata Kuliah & CPMK</h2>

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
                    
                    <!-- Header Actions -->
                    <div class="flex justify-between items-center border-b border-green-600 mb-6 pb-2">
                        <h3 class="text-lg font-bold text-gray-900">Informasi Mata Kuliah</h3>
                        <a href="{{ route('courses.index') }}" class="text-sm text-gray-600 hover:text-green-600 font-medium">
                            &larr; Kembali ke Daftar MK
                        </a>
                    </div>

                    <!-- Course Info Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <!-- Left Column: Basic Info -->
                        <div class="space-y-4">
                            <div>
                                <span class="block text-sm font-semibold text-gray-500">Kode Mata Kuliah</span>
                                <span class="text-lg font-bold text-gray-900">{{ $course->code }}</span>
                            </div>
                            <div>
                                <span class="block text-sm font-semibold text-gray-500">Nama Mata Kuliah</span>
                                <span class="text-lg font-bold text-gray-900">{{ $course->name }}</span>
                            </div>
                            <div class="flex space-x-8">
                                <div>
                                    <span class="block text-sm font-semibold text-gray-500">SKS</span>
                                    <span class="text-lg font-bold text-gray-900">{{ $course->sks }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-semibold text-gray-500">Semester</span>
                                    <span class="text-lg font-bold text-gray-900">{{ $course->semester }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Prerequisite & CPL -->
                        <div class="space-y-4">
                            <div>
                                <span class="block text-sm font-semibold text-gray-500">Mata Kuliah Prasyarat</span>
                                @if($course->prerequisite)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        {{ $course->prerequisite->code }} - {{ $course->prerequisite->name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic text-sm">- Tidak ada prasyarat -</span>
                                @endif
                            </div>
                            <div>
                                <span class="block text-sm font-semibold text-gray-500 mb-1">CPL yang Didukung</span>
                                @if($course->cpls->count() > 0)
                                    <div class="bg-gray-50 rounded-md p-3 border border-gray-100 max-h-40 overflow-y-auto">
                                        <ul class="space-y-1">
                                            @foreach($course->cpls as $cpl)
                                                <li class="text-sm text-gray-700 flex items-start">
                                                    <span class="font-bold mr-2 text-green-600">{{ $cpl->code }}</span>
                                                    <span>{{ Str::limit($cpl->description, 100) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-sm">- Belum ada CPL -</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-6 mt-10">
                        <h3 class="text-lg font-bold text-gray-900 border-l-4 border-green-500 pl-3">
                            Daftar CPMK & Indikator
                        </h3>
                    </div>

                    <div class="space-y-6">
                        @forelse($course->cpmks as $cpmk)
                            <div class="bg-white border text-card-foreground shadow-sm rounded-lg overflow-hidden border-gray-200 hover:border-green-300 transition-colors duration-200">
                                <!-- Card Header -->
                                <div class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="group flex items-start gap-3">
                                            <span class="flex-shrink-0 bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded border border-green-200 mt-1">
                                                {{ $cpmk->code }}
                                            </span>
                                            <p class="text-gray-900 font-semibold text-lg leading-snug">
                                                {{ $cpmk->description }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-6">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                        <!-- Lecturer Info -->
                                        <div class="md:col-span-4 lg:col-span-3 border-b md:border-b-0 md:border-r border-gray-100 pb-4 md:pb-0 md:pr-4">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Dosen Pengampu</p>
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-sm">
                                                    {{ $cpmk->lecturer ? substr($cpmk->lecturer->name, 0, 2) : '?' }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $cpmk->lecturer ? $cpmk->lecturer->name : 'Belum Ditentukan' }}
                                                    </p>
                                                    <p class="text-xs text-gray-500">Dosen Pengampu</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Indicators List -->
                                        <div class="md:col-span-8 lg:col-span-9">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Indikator</p>
                                            @if($cpmk->indicators->count() > 0)
                                                <ul class="space-y-3">
                                                    @foreach($cpmk->indicators as $idx => $indicator)
                                                        <li class="flex items-start text-sm text-gray-700 bg-gray-50 p-2 rounded border border-gray-100">
                                                            <span class="text-green-600 font-bold mr-2 mt-0.5">•</span>
                                                            <span class="leading-relaxed">{{ $indicator->description }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p class="text-sm text-gray-400 italic bg-gray-50 p-3 rounded border border-dashed border-gray-200">
                                                    Belum ada indikator yang ditambahkan.
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada CPMK</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan Capaian Pembelajaran Mata Kuliah.</p>
                                <div class="mt-6">
                                    <a href="{{ route('cpmks.create', $course) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                        + Tambah CPMK
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Footer Action -->


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
