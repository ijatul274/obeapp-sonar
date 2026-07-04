<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h2>

            <!-- Matrix Course vs CPL -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-3 sm:mb-4">Pemetaan CPL dalam Mata Kuliah</h3>
                    
                    <!-- Mobile Scroll Hint -->
                    <div class="lg:hidden mb-2 text-xs text-gray-500 italic flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                        Geser ke kanan untuk melihat lebih banyak CPL
                    </div>

                    <div class="overflow-x-auto -mx-4 sm:mx-0">
                        <div class="inline-block min-w-full align-middle max-w-full">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="sticky left-0 z-10 bg-gray-800 px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-600 w-48 sm:w-64">Mata Kuliah</th>
                                        @foreach($cpls as $cpl)
                                            <th class="px-2 py-2 sm:py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-600" title="{{ $cpl->description }}">
                                                {{ $cpl->code }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($courses as $course)
                                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                            <td class="sticky left-0 z-10 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }} px-3 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm text-gray-900 border-r border-gray-200">
                                                <div class="font-bold">{{ $course->code }}</div>
                                                <div class="text-xs text-gray-600 line-clamp-2">{{ $course->name }}</div>
                                            </td>
                                            @foreach($cpls as $cpl)
                                                <td class="px-2 py-2 sm:py-3 text-center text-sm text-gray-900 border-r border-gray-200">
                                                    @if($course->cpls->contains($cpl->id))
                                                        <span class="inline-flex items-center justify-center h-5 w-5 sm:h-6 sm:w-6 rounded-full bg-green-100 text-green-800">
                                                            <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        </span>
                                                    @else
                                                        <span class="text-gray-300">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $cpls->count() + 1 }}" class="px-6 py-4 text-center text-gray-500">Belum ada data mata kuliah.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
