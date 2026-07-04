<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Admin</h2>

            <!-- User Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4 sm:gap-6 mb-8">
                <!-- Total Users Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-4 sm:p-6 flex items-center">
                    <div class="p-3 sm:p-4 bg-purple-600 rounded-lg mr-3 sm:mr-4">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $userStats['total'] }}</p>
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Total User</p>
                    </div>
                </div>

                <!-- Admin Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-4 sm:p-6 flex items-center">
                    <div class="p-3 sm:p-4 bg-green-600 rounded-lg mr-3 sm:mr-4">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $userStats['admin'] }}</p>
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Admin</p>
                    </div>
                </div>

                <!-- Kaprodi Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-4 sm:p-6 flex items-center">
                    <div class="p-3 sm:p-4 bg-teal-600 rounded-lg mr-3 sm:mr-4">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $userStats['kaprodi'] }}</p>
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Kaprodi</p>
                    </div>
                </div>

                <!-- Dosen Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-4 sm:p-6 flex items-center">
                    <div class="p-3 sm:p-4 bg-yellow-400 rounded-lg mr-3 sm:mr-4">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $userStats['dosen'] }}</p>
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Dosen</p>
                    </div>
                </div>

                 <!-- Mahasiswa Card -->
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 p-4 sm:p-6 flex items-center">
                    <div class="p-3 sm:p-4 bg-red-600 rounded-lg mr-3 sm:mr-4">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-9-5 9 5 9-5-9 5z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $userStats['mahasiswa'] }}</p>
                        <p class="text-xs sm:text-sm font-medium text-gray-500">Mahasiswa</p>
                    </div>
                </div>
            </div>


        </div>
    </div>
</x-app-layout>
