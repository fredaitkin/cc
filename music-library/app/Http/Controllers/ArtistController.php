<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    public function index(Request $request)
    {
        $artists = Artist::withCount('songs')
            ->when($request->q, fn($q, $v) => $q->where('name', 'like', "%$v%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('artists.index', compact('artists'));
    }

    public function show(Artist $artist)
    {
        $artist->load(['albums.songs']);

        return view('artists.show', compact('artist'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'bio'  => 'nullable|string',
        ]);

        Artist::create($data);

        return redirect()->route('artists.index')->with('success', 'Artist added.');
    }

    public function destroy(Artist $artist)
    {
        $artist->delete();

        return redirect()->route('artists.index')->with('success', 'Artist removed.');
    }
}
