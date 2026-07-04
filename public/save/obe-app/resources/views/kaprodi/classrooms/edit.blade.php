<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Detail Kelas</h2>
                <a href="{{ route('classrooms.index') }}" class="text-gray-600 hover:text-gray-900">
                    &larr; Kembali ke Daftar Kelas
                </a>
            </div>

            <!-- Classroom Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-blue-50">
                    <h3 class="text-lg font-bold text-gray-900">Informasi Kelas</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nama Kelas</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $classroom->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Semester</label>
                            <p class="text-lg font-semibold text-gray-900">Semester {{ $classroom->semester }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Kode Enrollment</label>
                            <div class="flex items-center gap-2">
                                <code class="bg-gray-100 px-3 py-2 rounded font-mono text-green-700 font-bold text-lg">{{ $classroom->enrollment_code }}</code>
                                <button onclick="navigator.clipboard.writeText('{{ $classroom->enrollment_code }}'); this.textContent='✓ Copied!'; setTimeout(() => this.textContent='Salin', 2000)" 
                                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Salin
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Mata Kuliah</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Kode Mata Kuliah</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $classroom->course->code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nama Mata Kuliah</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $classroom->course->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">SKS</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $classroom->course->sks }} SKS</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lecturers Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Dosen Pengampu</h3>
                </div>
                <div class="p-6">
                    @php
                        // Get unique lecturers from all CPMKs of this course
                        $lecturerIds = $classroom->course->cpmks->pluck('lecturer_id')->unique()->filter();
                        $lecturers = \App\Models\User::whereIn('id', $lecturerIds)->get();
                    @endphp
                    @if($lecturers->count() > 0)
                        <div class="space-y-3">
                            @foreach($lecturers as $lecturer)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-green-600 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($lecturer->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $lecturer->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $lecturer->identity }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">Belum ada dosen pengampu yang ditugaskan untuk mata kuliah ini.</p>
                    @endif
                </div>
            </div>

            <!-- Enrolled Students Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Mahasiswa Terdaftar ({{ $classroom->students->count() }})</h3>
                </div>
                <div class="p-6">
                    @if($classroom->students->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mahasiswa</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($classroom->students as $idx => $student)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $idx + 1 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $student->identity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $student->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $student->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form action="{{ route('classrooms.unenroll', [$classroom, $student]) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus mahasiswa ini dari kelas?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="mt-4 text-sm text-gray-500 italic">Belum ada mahasiswa yang terdaftar di kelas ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
