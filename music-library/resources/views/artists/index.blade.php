<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Artists</h2>
    </x-slot>

    <div class="space-y-6">
        {{-- Search --}}
        <form method="GET" action="{{ route('artists.index') }}" class="flex gap-3">
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Search artists…"
                class="flex-1 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">Search</button>
            @if(request('q'))
                <a href="{{ route('artists.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Clear</a>
            @endif
        </form>

        {{-- Add Artist (admin only) --}}
        @auth
            <div x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                    + Add Artist
                </button>
                <div x-show="open" x-cloak class="mt-3 bg-white rounded shadow p-4 max-w-lg">
                    <form method="POST" action="{{ route('artists.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" name="name" required class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bio</label>
                            <textarea name="bio" rows="3" class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">Save Artist</button>
                    </form>
                </div>
            </div>
        @endauth

        {{-- Artist List --}}
        @if ($artists->isEmpty())
            <p class="text-gray-500">No artists found.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($artists as $artist)
                    <div class="bg-white rounded shadow p-4 flex flex-col justify-between">
                        <div>
                            <a href="{{ route('artists.show', $artist) }}"
                               class="text-lg font-semibold text-indigo-700 hover:underline">
                                {{ $artist->name }}
                            </a>
                            <p class="text-sm text-gray-500 mt-1">{{ $artist->songs_count }} song(s)</p>
                            @if ($artist->bio)
                                <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $artist->bio }}</p>
                            @endif
                        </div>
                        @auth
                            <form method="POST" action="{{ route('artists.destroy', $artist) }}" class="mt-3"
                                  onsubmit="return confirm('Remove {{ addslashes($artist->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                            </form>
                        @endauth
                    </div>
                @endforeach
            </div>
            <div>{{ $artists->links() }}</div>
        @endif
    </div>
</x-app-layout>
