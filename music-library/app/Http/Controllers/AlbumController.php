<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public function index(Request $request)
    {
        $albums = Album::with('artist')
            ->withCount('songs')
            ->when($request->q, fn($q, $v) => $q->where('name', 'like', "%$v%"))
            ->when($request->artist_id, fn($q, $v) => $q->where('artist_id', $v))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $artists = Artist::orderBy('name')->get();

        return view('albums.index', compact('albums', 'artists'));
    }

    public function show(Album $album)
    {
        $album->load(['artist', 'songs']);

        return view('albums.show', compact('album'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'release_year' => 'nullable|integer|min:1900|max:2099',
            'artist_id'    => 'required|exists:artists,id',
        ]);

        Album::create($data);

        return redirect()->route('albums.index')->with('success', 'Album added.');
    }

    public function destroy(Album $album)
    {
        $album->delete();

        return redirect()->route('albums.index')->with('success', 'Album removed.');
    }
}
