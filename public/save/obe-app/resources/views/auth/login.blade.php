<x-guest-layout>
    <div class="flex min-h-screen">
        <!-- Left Side - Image -->
        <div class="hidden lg:flex w-1/2 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=2070&auto=format&fit=crop');">
            <!-- Overlay if needed -->
            <div class="w-full h-full bg-black/20"></div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-lg">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-10">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2c/LOGO-UNRI.png" alt="Logo" class="h-16 w-auto">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                            {{ \App\Models\Setting::where('key', 'login_title')->value('value') ?? 'Aplikasi Pengelolaan Nilai Kurikulum OBE' }}
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ \App\Models\Setting::where('key', 'login_description')->value('value') ?? '' }}
                        </p>
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- NIP/NIM -->
                    <div>
                        <label for="identity" class="block text-sm font-medium text-gray-700 mb-1">
                            NIP/NIM <span class="text-red-500">*</span>
                        </label>
                        <input id="identity" type="text" name="identity" value="2207111385" required autofocus
                            class="w-full rounded-md border border-gray-300 bg-blue-50 px-3 py-2 text-base text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600"
                            placeholder="Masukkan NIP atau NIM">
                        @error('identity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div x-data="{ show: false }">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                                class="w-full rounded-md border border-gray-300 bg-blue-50 px-3 py-2 text-base text-gray-900 focus:border-green-600 focus:outline-none focus:ring-1 focus:ring-green-600"
                                placeholder="*************">
                            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <!-- Icon Mata Tertutup (Show Password) -->
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <!-- Icon Mata Terbuka (Hide Password) -->
                                <svg x-show="show" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Button -->
                    <div>
                        <button type="submit" class="flex w-full justify-center rounded-md bg-green-600 px-3 py-2.5 text-base font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
