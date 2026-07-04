<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>
    <style>
        /* Sembunyikan icon mata bawaan browser (Edge/Chrome/IE) agar tidak bentrok dengan custom toggle */
        input::-ms-reveal,
        input::-ms-clear,
        input::-webkit-contacts-auto-fill-button,
        input::-webkit-credentials-auto-fill-button {
            display: none !important;
        }
    </style>
</head>
<body class="font-sans antialiased h-full flex flex-col">
    
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50" x-data="{ open: false, mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Left Side -->
                <div class="flex">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2c/LOGO-UNRI.png" alt="Logo" class="block h-8 sm:h-10 w-auto">
                    </div>

                    <!-- Navigation Links (Desktop) -->
                    <div class="hidden space-x-8 sm:-my-px sm:ml-10 lg:flex">
                        @if(Auth::user()->role === 'admin')
                            <!-- Admin Menu -->
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.dashboard') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }} text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out">
                                DASHBOARD
                            </a>
                            <a href="{{ route('users.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('users.index') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }} text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out">
                                KELOLA AKUN
                            </a>
                            <a href="{{ route('settings.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('settings.index') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }} text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out">
                                KELOLA SISTEM
                            </a>
                        @elseif(Auth::user()->role === 'dosen')
                            <!-- Dosen Menu -->
                            <a href="{{ route('dosen.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dosen.dashboard') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }} text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out">
                                DASHBOARD
                            </a>
                            <a href="{{ route('dosen.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-bold leading-5 text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                MATAKULIAH
                            </a>
                            <a href="#" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-bold leading-5 text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                LAPORAN
                            </a>
                        @elseif(Auth::user()->role === 'mahasiswa')
                            <!-- Mahasiswa Menu -->
                            <a href="{{ route('mahasiswa.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('mahasiswa.dashboard') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }} text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out">
                                DASHBOARD
                            </a>
                            <a href="#" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-bold leading-5 text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                NILAI
                            </a>
                        @else
                            <!-- Kaprodi Menu -->
                            <a href="{{ route('kaprodi.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('kaprodi.dashboard') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }} text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out">
                                DASHBOARD
                            </a>
                            
                            <!-- Dropdown Kelola -->
                            <div class="hidden lg:flex lg:items-center lg:ml-6">
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button @click="open = !open" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('graduate-profiles.*') || request()->routeIs('cpls.*') || request()->routeIs('courses.*') ? 'border-green-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700' }} text-sm font-bold leading-5 focus:outline-none transition duration-150 ease-in-out">
                                        <span>KELOLA</span>
                                        <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div x-show="open" style="display: none;" class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-left left-0 bg-white ring-1 ring-black ring-opacity-5 py-1">
                                        <a href="{{ route('graduate-profiles.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Lulusan</a>
                                        <a href="{{ route('cpls.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">CPL</a>
                                        <a href="{{ route('courses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mata Kuliah</a>
                                        <a href="{{ route('classrooms.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Kelola Kelas</a>
                                    </div>
                                </div>
                            </div>

                            <a href="#" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-bold leading-5 text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                LAPORAN
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Right Side (User Menu Desktop + Mobile Hamburger) -->
                <div class="flex items-center">
                    <!-- Desktop User Menu -->
                    <div class="hidden lg:flex lg:items-center lg:ml-6">
                        <div class="relative" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-bold rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <div>AKUN</div>
                                <div class="ml-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                            <div x-show="open" style="display: none;" class="absolute z-50 mt-2 w-48 rounded-md shadow-lg origin-top-right right-0 bg-white ring-1 ring-black ring-opacity-5 py-1">
                                <!-- Profile Link -->
                                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-b border-gray-100">
                                    {{ Auth::user()->name }}
                                </a>
                                
                                <!-- Logout -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Log Out
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Hamburger Button -->
                    <div class="lg:hidden flex items-center ml-2">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen}" class="hidden lg:hidden border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                @if(Auth::user()->role === 'admin')
                    <!-- Admin Mobile Menu -->
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-bold {{ request()->routeIs('admin.dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        DASHBOARD
                    </a>
                    <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded-md text-base font-bold {{ request()->routeIs('users.index') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        KELOLA AKUN
                    </a>
                    <a href="{{ route('settings.index') }}" class="block px-3 py-2 rounded-md text-base font-bold {{ request()->routeIs('settings.index') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        KELOLA SISTEM
                    </a>
                @elseif(Auth::user()->role === 'dosen')
                    <!-- Dosen Mobile Menu -->
                    <a href="{{ route('dosen.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-bold {{ request()->routeIs('dosen.dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        DASHBOARD
                    </a>
                    <a href="{{ route('dosen.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:bg-gray-100">
                        MATAKULIAH
                    </a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:bg-gray-100">
                        LAPORAN
                    </a>
                @elseif(Auth::user()->role === 'mahasiswa')
                    <!-- Mahasiswa Mobile Menu -->
                    <a href="{{ route('mahasiswa.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-bold {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        DASHBOARD
                    </a>
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:bg-gray-100">
                        NILAI
                    </a>
                @else
                    <!-- Kaprodi Mobile Menu -->
                    <a href="{{ route('kaprodi.dashboard') }}" class="block px-3 py-2 rounded-md text-base font-bold {{ request()->routeIs('kaprodi.dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                        DASHBOARD
                    </a>
                    
                    <!-- Mobile Kelola Submenu -->
                    <div class="space-y-1">
                        <div class="px-3 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Kelola</div>
                        <a href="{{ route('graduate-profiles.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('graduate-profiles.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                            Profil Lulusan
                        </a>
                        <a href="{{ route('cpls.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('cpls.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                            CPL
                        </a>
                        <a href="{{ route('courses.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('courses.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                            Mata Kuliah
                        </a>
                        <a href="{{ route('classrooms.index') }}" class="block px-3 py-2 rounded-md text-sm {{ request()->routeIs('classrooms.*') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-100' }}">
                            Kelola Kelas
                        </a>
                    </div>

                    <a href="#" class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 hover:bg-gray-100">
                        LAPORAN
                    </a>
                @endif
            </div>

            <!-- Mobile User Section -->
            <div class="pt-4 pb-3 border-t border-gray-200">
                <div class="px-4">
                    <div class="text-base font-bold text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-100">
                        Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="block px-4 py-2 text-base font-medium text-gray-700 hover:bg-gray-100">
                            Log Out
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-3 sm:py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs sm:text-sm font-semibold">
            {{ \App\Models\Setting::where('key', 'footer_text')->value('value') ?? 'Program Studi Teknik Informatika UNRI 2025' }}
        </div>
    </footer>
</body>
</html>
