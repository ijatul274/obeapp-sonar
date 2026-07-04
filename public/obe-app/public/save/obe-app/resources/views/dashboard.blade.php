<x-guest-layout>
    <div class="p-8">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <p class="mt-4">You are logged in!</p>
        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Logout</button>
        </form>
    </div>
</x-guest-layout>
