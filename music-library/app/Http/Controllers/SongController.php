<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $songs = Song::with(['album.artist'])
            ->when($request->q, fn($q, $v) => $q->where('title', 'like', "%$v%"))
            ->when($request->genre, fn($q, $v) => $q->where('genre', $v))
            ->when($request->artist_id, fn($q, $v) => $q->whereHas('album', fn($q) => $q->where('artist_id', $v)))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $artists = Artist::orderBy('name')->get();
        $genres  = Song::whereNotNull('genre')->distinct()->orderBy('genre')->pluck('genre');
        $albums  = Album::with('artist')->orderBy('name')->get();

        return view('songs.index', compact('songs', 'artists', 'genres', 'albums'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'album_id'     => 'required|exists:albums,id',
            'duration'     => 'nullable|string|max:10',
            'genre'        => 'nullable|string|max:100',
            'release_year' => 'nullable|integer|min:1900|max:2099',
        ]);

        Song::create($data);

        return redirect()->route('songs.index')->with('success', 'Song added.');
    }

    public function destroy(Song $song)
    {
        $song->delete();

        return redirect()->route('songs.index')->with('success', 'Song removed.');
    }
}
