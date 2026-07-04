<x-app-layout>
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Mahasiswa</h2>

            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="mb-4 flex items-center bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm rounded-r" role="alert">
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 flex items-center bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow-sm rounded-r" role="alert">
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            @endif

            <!-- Enrollment Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-4 sm:p-6 bg-gradient-to-r from-green-50 to-blue-50">
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2 sm:mb-3">Bergabung ke Kelas</h3>
                    <p class="text-xs sm:text-sm text-gray-600 mb-3 sm:mb-4">Masukkan kode enrollment yang diberikan oleh dosen/kaprodi untuk bergabung ke kelas.</p>
                    
                    <form action="{{ route('mahasiswa.enroll') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        @csrf
                        <div class="flex-1">
                            <input type="text" 
                                   name="enrollment_code" 
                                   id="enrollment_code" 
                                   placeholder="Masukkan kode 8 karakter" 
                                   required 
                                   maxlength="8"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-3 px-4 text-sm uppercase font-mono">
                        </div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded shadow-sm whitespace-nowrap w-full sm:w-auto">
                            Bergabung
                        </button>
                    </form>
                </div>
            </div>

            <!-- Enrolled Classes Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Mata Kuliah yang Diikuti</h3>
                </div>
                <div class="p-6">
                    @forelse($classrooms as $classroom)
                        <div class="border border-gray-200 rounded-lg p-4 mb-3 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900">{{ $classroom->course->name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $classroom->course->code }} - {{ $classroom->name }}</p>
                                    <div class="flex gap-4 mt-2">
                                        <span class="text-xs text-gray-500">
                                            <span class="font-semibold">SKS:</span> {{ $classroom->course->sks }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            <span class="font-semibold">Semester:</span> {{ $classroom->semester }}
                                        </span>
                                    </div>
                                </div>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                                    Terdaftar
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <p class="mt-4 text-sm text-gray-500 italic">Anda belum terdaftar di kelas manapun.</p>
                            <p class="text-xs text-gray-400 mt-1">Gunakan kode enrollment di atas untuk bergabung.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
