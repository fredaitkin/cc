<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Albums</h2>
    </x-slot>

    <div class="space-y-6">
        {{-- Search / Filter --}}
        <form method="GET" action="{{ route('albums.index') }}" class="flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Search albums…"
                class="rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
            <select name="artist_id" class="rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                <option value="">All Artists</option>
                @foreach ($artists as $artist)
                    <option value="{{ $artist->id }}" @selected(request('artist_id') == $artist->id)>{{ $artist->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">Filter</button>
            @if(request()->anyFilled(['q','artist_id']))
                <a href="{{ route('albums.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Clear</a>
            @endif
        </form>

        {{-- Add Album (admin only) --}}
        @auth
            <div x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                    + Add Album
                </button>
                <div x-show="open" x-cloak class="mt-3 bg-white rounded shadow p-4 max-w-lg">
                    <form method="POST" action="{{ route('albums.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" name="name" required class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Artist *</label>
                            <select name="artist_id" required class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                                <option value="">Select artist…</option>
                                @foreach ($artists as $artist)
                                    <option value="{{ $artist->id }}">{{ $artist->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Release Year</label>
                            <input type="number" name="release_year" min="1900" max="2099"
                                class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">Save Album</button>
                    </form>
                </div>
            </div>
        @endauth

        {{-- Album List --}}
        @if ($albums->isEmpty())
            <p class="text-gray-500">No albums found.</p>
        @else
            <div class="bg-white rounded shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Album</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Artist</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Year</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Songs</th>
                            @auth<th class="px-4 py-3"></th>@endauth
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($albums as $album)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('albums.show', $album) }}" class="text-indigo-700 hover:underline font-medium">
                                        {{ $album->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('artists.show', $album->artist) }}" class="text-gray-600 hover:underline">
                                        {{ $album->artist->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $album->release_year ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $album->songs_count }}</td>
                                @auth
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('albums.destroy', $album) }}"
                                              onsubmit="return confirm('Remove {{ addslashes($album->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline text-xs">Remove</button>
                                        </form>
                                    </td>
                                @endauth
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $albums->links() }}</div>
        @endif
    </div>
</x-app-layout>
