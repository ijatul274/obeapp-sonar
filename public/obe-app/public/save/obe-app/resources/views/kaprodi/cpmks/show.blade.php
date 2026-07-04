<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Detail CPMK</h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="border-b-2 border-green-500 mb-6">
                        <h3 class="text-xl font-bold text-gray-800 pb-2">Informasi Umum</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mata Kuliah</p>
                            <p class="text-gray-900 font-semibold text-lg">{{ $cpmk->course->code }} - {{ $cpmk->course->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Dosen Pengampu</p>
                            <p class="text-gray-900 text-lg">
                                @if($cpmk->lecturer)
                                    {{ $cpmk->lecturer->name }}
                                @else
                                    <span class="text-gray-400 italic">Tidak ada dosen spesifik</span>
                                @endif
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Kode CPMK</p>
                            <p class="text-gray-900 font-bold text-xl">{{ $cpmk->code }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Pernyataan CPMK</p>
                            <p class="text-gray-900 leading-relaxed bg-gray-50 p-4 rounded-md border border-gray-100 mt-1">
                                {{ $cpmk->description }}
                            </p>
                        </div>
                    </div>

                    <div class="border-b-2 border-green-500 mb-6">
                        <h3 class="text-xl font-bold text-gray-800 pb-2">Indikator Kinerja</h3>
                    </div>

                    @if($cpmk->indicators->count() > 0)
                        <div class="bg-white rounded-md border border-gray-200 overflow-hidden">
                            <ul class="divide-y divide-gray-200">
                                @foreach($cpmk->indicators as $index => $indicator)
                                    <li class="p-4 hover:bg-gray-50 transition duration-150">
                                        <div class="flex items-start">
                                            <span class="flex-shrink-0 bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-full mr-3 border border-green-200">
                                                {{ $index + 1 }}
                                            </span>
                                            <p class="text-gray-700">{{ $indicator->description }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-6 bg-gray-50 rounded-md border border-gray-100 border-dashed">
                            <p class="text-gray-500 italic">Belum ada indikator untuk CPMK ini.</p>
                        </div>
                    @endif

                    <!-- Footer Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-100 flex justify-between items-center">
                        <div class="flex space-x-3">
                            <!-- Edit Button -->
                            <a href="{{ route('cpmks.edit', $cpmk) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>

                            <!-- Delete Button -->
                            <form action="{{ route('cpmks.destroy', $cpmk) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus CPMK ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Hapus
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('courses.show', $cpmk->course_id) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
