<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Kelola Komponen Penilaian</h2>
                <a href="{{ route('dosen.courses.show', $indicator->cpmk->course_id) }}" class="text-gray-600 hover:text-gray-900">
                    &larr; Kembali ke Detail MK
                </a>
            </div>

            <!-- Indicator Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Indikator</h3>
                    <p class="text-gray-700 bg-gray-50 p-4 rounded border border-gray-100">
                        {{ $indicator->description }}
                    </p>
                    <div class="mt-2 text-sm text-gray-500">
                        CPMK: <span class="font-medium text-gray-700">{{ $indicator->cpmk->code }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- List Assessments -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-900">Daftar Komponen Penilaian</h3>
                            <span class="text-sm font-medium px-3 py-1 bg-green-100 text-green-800 rounded-full">
                                Total Bobot: {{ $indicator->assessments->sum('percentage') }}%
                            </span>
                        </div>
                        <div class="p-6">
                            @if($indicator->assessments->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Komponen</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bobot</th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($indicator->assessments as $assessment)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        <a href="{{ route('assessments.scores.index', $assessment) }}" class="text-green-600 hover:text-green-900 hover:underline">
                                                            {{ $assessment->name }}
                                                        </a>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        {{ $assessment->description ?? '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ number_format($assessment->percentage, 2) }}%
                                                        @if(!$assessment->is_auto)
                                                            <span class="text-xs text-amber-600 font-semibold ml-1">(Manual)</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <form action="{{ route('assessments.destroy', $assessment) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus komponen ini?');">
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
                                <p class="text-center text-gray-500 py-8 italic">Belum ada komponen penilaian.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Add Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Tambah Komponen</h3>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('assessments.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="indicator_id" value="{{ $indicator->id }}">
                                
                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Komponen</label>
                                    <input type="text" name="name" id="name" required placeholder="Contoh: Tugas 1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-3 px-4 text-sm">
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi & Ketentuan</label>
                                    <textarea name="description" id="description" rows="3" placeholder="Deskripsi tugas atau asesmen..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-3 px-4 text-sm"></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="percentage" class="block text-sm font-medium text-gray-700 mb-1">Bobot (%)</label>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="percentage" id="percentage" min="0" max="100" step="0.01" placeholder="Auto" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-3 px-4 text-sm">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Biarkan kosong untuk hitung otomatis.</p>
                                </div>

                                <div class="mb-4 bg-blue-50 p-3 rounded text-xs text-blue-700">
                                    Jika bobot dikosongkan, sistem akan membagi sisa persentase secara merata ke komponen otomatis.
                                </div>

                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow-sm text-sm">
                                    Simpan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
