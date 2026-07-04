<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Kelola Profil Lulusan</h2>

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
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <!-- Header Actions -->
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('graduate-profiles.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center">
                            <span class="mr-2">+</span> Tambah PL
                        </a>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider w-16 text-center border-r border-gray-600">
                                        No
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider w-1/3 border-r border-gray-600">
                                        Profil Lulusan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider border-r border-gray-600">
                                        Deskripsi
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider w-32">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($profiles as $index => $profile)
                                <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center border-r border-gray-200">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200">
                                        {{ $profile->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200">
                                        {{ Str::limit($profile->description, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('graduate-profiles.edit', $profile) }}" class="text-blue-600 hover:text-blue-900" title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <form action="{{ route('graduate-profiles.destroy', $profile) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus profil lulusan ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        Belum ada data profil lulusan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination (Static for now, matching design style) -->
                    <div class="flex items-center justify-end mt-4 space-x-2">
                        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-600 bg-white hover:bg-gray-50">Sebelumnya</button>
                        <button class="px-3 py-1 border border-gray-300 rounded text-sm bg-gray-200 text-gray-700">1</button>
                        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-600 bg-white hover:bg-gray-50">Selanjutnya</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
