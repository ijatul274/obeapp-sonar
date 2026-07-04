<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Input Nilai: {{ $assessment->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $assessment->indicator->cpmk->course->name }} - {{ $assessment->indicator->cpmk->course->code }}
                    </p>
                </div>
                <a href="{{ route('dosen.indicators.edit', $assessment->indicator_id) }}" class="text-gray-600 hover:text-gray-900">
                    &larr; Kembali ke Indikator
                </a>
            </div>

            <!-- Grading Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900">Daftar Mahasiswa</h3>
                    <div class="text-sm text-gray-500">
                        Bobot Komponen: <span class="font-bold text-gray-800">{{ $assessment->percentage }}%</span>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('assessments.scores.store', $assessment) }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mahasiswa</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Nilai (0-100)</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($students as $idx => $student)
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
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" 
                                                       name="scores[{{ $student->id }}]" 
                                                       value="{{ $scores[$student->id] ?? '' }}" 
                                                       min="0" max="100" step="0.01" 
                                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-2 px-3 text-sm"
                                                       placeholder="-">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                 <!-- Aksi Hapus Nilai (Jika ada nilai) -->
                                                 <!-- Using input clear logic via javascript or just Delete button that submits a delete form? 
                                                      User wanted 'edit and delete' buttons. 
                                                      Since input is editable directly, 'Edit' button is implicit.
                                                      'Delete' button should probably clear the input or delete the record.
                                                 -->
                                                 <button type="button" onclick="document.querySelector('input[name=\'scores[{{ $student->id }}]\']').value = '';" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                    Hapus
                                                 </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center italic">
                                                Belum ada mahasiswa yang mengambil mata kuliah ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($students->count() > 0)
                            <div class="flex justify-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded shadow-sm">
                                    Simpan Nilai
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
