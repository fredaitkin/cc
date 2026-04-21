<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('artists.show', $album->artist) }}" class="text-indigo-600 hover:underline">{{ $album->artist->name }}</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800">{{ $album->name }}</h2>
            @if ($album->release_year)
                <span class="text-gray-500 text-base">({{ $album->release_year }})</span>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Add Song (admin only) --}}
        @auth
            <div x-data="{ open: false }">
                <button @click="open = !open" class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                    + Add Song to This Album
                </button>
                <div x-show="open" x-cloak class="mt-3 bg-white rounded shadow p-4 max-w-lg">
                    <form method="POST" action="{{ route('songs.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="album_id" value="{{ $album->id }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title *</label>
                            <input type="text" name="title" required class="mt-1 w-full rounded border-gray-300 shadow-sm text-sm px-3 py-2 border">
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
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">#</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Genre</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Duration</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Year</th>
                        @auth<th class="px-4 py-3"></th>@endauth
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($album->songs as $i => $song)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $song->title }}</td>
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
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-gray-400 text-center">No songs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
