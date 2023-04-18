<x-guest>
    <p>Logged In</p>
    <p>{{ auth()->user()->name }}</p>

    {{-- logout --}}
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Logout</button>
    </form>
</x-guest>
