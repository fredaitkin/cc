<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SongController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('songs.index'));

// Public browsing
Route::get('/artists', [ArtistController::class, 'index'])->name('artists.index');
Route::get('/artists/{artist}', [ArtistController::class, 'show'])->name('artists.show');
Route::get('/albums', [AlbumController::class, 'index'])->name('albums.index');
Route::get('/albums/{album}', [AlbumController::class, 'show'])->name('albums.show');
Route::get('/songs', [SongController::class, 'index'])->name('songs.index');

// Admin writes
Route::middleware('auth')->group(function () {
    Route::post('/artists', [ArtistController::class, 'store'])->name('artists.store');
    Route::delete('/artists/{artist}', [ArtistController::class, 'destroy'])->name('artists.destroy');
    Route::post('/albums', [AlbumController::class, 'store'])->name('albums.store');
    Route::delete('/albums/{album}', [AlbumController::class, 'destroy'])->name('albums.destroy');
    Route::post('/songs', [SongController::class, 'store'])->name('songs.store');
    Route::delete('/songs/{song}', [SongController::class, 'destroy'])->name('songs.destroy');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
