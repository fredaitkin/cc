<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Songs</h2>
    </x-slot>

    <div class="space-y-6">
        {{-- Search / Filter --}}
        <form method="GET" action="{{ route('songs.index') }}" class="flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Search songs…"
                class="rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
            <select name="artist_id" class="rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                <option value="">All Artists</option>
                @foreach ($artists as $artist)
                    <option value="{{ $artist->id }}" @selected(request('artist_id') == $artist->id)>{{ $artist->name }}</option>
                @endforeach
            </select>
            <select name="genre" class="rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                <option value="">All Genres</option>
                @foreach ($genres as $genre)
                    <option value="{{ $genre }}" @selected(request('genre') === $genre)>{{ $genre }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">Filter</button>
            @if(request()->anyFilled(['q','artist_id','genre']))
                <a href="{{ route('songs.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">Clear</a>
            @endif
        </form>

        {{-- Add Song (admin only) --}}
        @auth
            <div x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                    + Add Song
                </button>
                <div x-show="open" x-cloak class="mt-3 bg-white rounded shadow p-4 max-w-xl">
                    <form method="POST" action="{{ route('songs.store') }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title *</label>
                            <input type="text" name="title" required class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Album *</label>
                            <select name="album_id" required class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                                <option value="">Select album…</option>
                                @foreach ($albums as $album)
                                    <option value="{{ $album->id }}">{{ $album->artist->name }} — {{ $album->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Duration</label>
                                <input type="text" name="duration" placeholder="3:45" maxlength="10"
                                    class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Genre</label>
                                <input type="text" name="genre" class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Year</label>
                                <input type="number" name="release_year" min="1900" max="2099"
                                    class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">Save Song</button>
                    </form>
                </div>
            </div>
        @endauth

        {{-- Song List --}}
        @if ($songs->isEmpty())
            <p class="text-gray-500">No songs found.</p>
        @else
            <div class="bg-white rounded shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Artist</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Album</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Genre</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Duration</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Year</th>
                            @auth<th class="px-4 py-3"></th>@endauth
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($songs as $song)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $song->title }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('artists.show', $song->album->artist) }}" class="text-indigo-700 hover:underline">
                                        {{ $song->album->artist->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('albums.show', $song->album) }}" class="text-gray-600 hover:underline">
                                        {{ $song->album->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $song->genre ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $song->duration ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $song->release_year ?? '—' }}</td>
                                @auth
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('songs.destroy', $song) }}"
                                              onsubmit="return confirm('Remove {{ addslashes($song->title) }}?')">
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
            <div>{{ $songs->links() }}</div>
        @endif
    </div>
</x-app-layout>
