<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Dosen</h2>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Mata Kuliah yang Diampu</h3>
                    
                    @if($courses->isEmpty())
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        Anda belum ditugaskan pada mata kuliah apapun oleh Kaprodi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Desktop Table View -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider w-16 border-r border-gray-600">
                                            No
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-600">
                                            Kode MK
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider border-r border-gray-600">
                                            Nama Mata Kuliah
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider w-24 border-r border-gray-600">
                                            SKS
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider w-24 border-r border-gray-600">
                                            Semester
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider w-32">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($courses as $index => $course)
                                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center border-r border-gray-200">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center border-r border-gray-200">
                                            {{ $course->code }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200 font-medium">
                                            {{ $course->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center border-r border-gray-200">
                                            {{ $course->sks }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center border-r border-gray-200">
                                            {{ $course->semester }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                            <a href="{{ route('dosen.courses.show', $course) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden space-y-3">
                            @foreach($courses as $index => $course)
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <div class="text-xs text-gray-500 mb-1">{{ $course->code }}</div>
                                        <h4 class="font-bold text-gray-900 text-sm">{{ $course->name }}</h4>
                                    </div>
                                    <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                        #{{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="flex gap-4 mt-3 text-xs text-gray-600">
                                    <span><span class="font-semibold">SKS:</span> {{ $course->sks }}</span>
                                    <span><span class="font-semibold">Semester:</span> {{ $course->semester }}</span>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('dosen.courses.show', $course) }}" class="block w-full text-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
