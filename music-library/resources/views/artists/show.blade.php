<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('artists.index') }}" class="text-indigo-600 hover:underline text-sm">Artists</a>
            <span class="text-gray-400">/</span>
            <h2 class="font-semibold text-xl text-gray-800">{{ $artist->name }}</h2>
        </div>
    </x-slot>

    <div class="space-y-8">
        {{-- Artist Bio --}}
        @if ($artist->bio)
            <div class="bg-white rounded shadow p-4">
                <h3 class="font-medium text-gray-700 mb-2">Bio</h3>
                <p class="text-gray-600 text-sm whitespace-pre-line">{{ $artist->bio }}</p>
            </div>
        @endif

        {{-- Albums --}}
        <div>
            <h3 class="font-semibold text-lg text-gray-800 mb-3">
                Albums ({{ $artist->albums->count() }})
            </h3>
            @if ($artist->albums->isEmpty())
                <p class="text-gray-500 text-sm">No albums yet.</p>
            @else
                <div class="space-y-4">
                    @foreach ($artist->albums as $album)
                        <div class="bg-white rounded shadow p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <a href="{{ route('albums.show', $album) }}"
                                       class="font-semibold text-indigo-700 hover:underline">
                                        {{ $album->name }}
                                    </a>
                                    @if ($album->release_year)
                                        <span class="text-sm text-gray-500 ml-2">{{ $album->release_year }}</span>
                                    @endif
                                    <p class="text-sm text-gray-500 mt-1">{{ $album->songs->count() }} song(s)</p>
                                </div>
                            </div>
                            @if ($album->songs->isNotEmpty())
                                <ul class="mt-3 divide-y divide-gray-100">
                                    @foreach ($album->songs as $song)
                                        <li class="py-1 flex justify-between text-sm">
                                            <span class="text-gray-800">{{ $song->title }}</span>
                                            <span class="text-gray-400">{{ $song->duration }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
