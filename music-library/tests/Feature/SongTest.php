<?php

use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use App\Models\User;

test('guest can view songs index', function () {
    $song = Song::factory()->create(['title' => 'My Test Song']);

    $response = $this->get(route('songs.index'));

    $response->assertOk()->assertSee('My Test Song');
});

test('guest cannot add a song', function () {
    $album = Album::factory()->create();

    $response = $this->post(route('songs.store'), [
        'title'    => 'Blocked Song',
        'album_id' => $album->id,
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseMissing('songs', ['title' => 'Blocked Song']);
});

test('guest cannot remove a song', function () {
    $song = Song::factory()->create();

    $response = $this->delete(route('songs.destroy', $song));

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('songs', ['id' => $song->id]);
});

test('admin can add a song', function () {
    $admin = User::factory()->create();
    $album = Album::factory()->create();

    $response = $this->actingAs($admin)->post(route('songs.store'), [
        'title'    => 'New Song',
        'album_id' => $album->id,
        'duration' => '3:45',
        'genre'    => 'Rock',
    ]);

    $response->assertRedirect(route('songs.index'));
    $this->assertDatabaseHas('songs', ['title' => 'New Song', 'album_id' => $album->id]);
});

test('admin can remove a song', function () {
    $admin = User::factory()->create();
    $song  = Song::factory()->create();

    $response = $this->actingAs($admin)->delete(route('songs.destroy', $song));

    $response->assertRedirect(route('songs.index'));
    $this->assertDatabaseMissing('songs', ['id' => $song->id]);
});

test('songs can be filtered by genre', function () {
    $rock = Song::factory()->create(['title' => 'Rock Song', 'genre' => 'Rock']);
    $jazz = Song::factory()->create(['title' => 'Jazz Song', 'genre' => 'Jazz']);

    $response = $this->get(route('songs.index', ['genre' => 'Rock']));

    $response->assertOk()->assertSee('Rock Song')->assertDontSee('Jazz Song');
});

test('songs can be filtered by artist', function () {
    $artist1 = Artist::factory()->create(['name' => 'Artist One']);
    $artist2 = Artist::factory()->create(['name' => 'Artist Two']);
    $album1  = Album::factory()->create(['artist_id' => $artist1->id]);
    $album2  = Album::factory()->create(['artist_id' => $artist2->id]);
    $song1   = Song::factory()->create(['title' => 'Song by One', 'album_id' => $album1->id]);
    $song2   = Song::factory()->create(['title' => 'Song by Two', 'album_id' => $album2->id]);

    $response = $this->get(route('songs.index', ['artist_id' => $artist1->id]));

    $response->assertOk()->assertSee('Song by One')->assertDontSee('Song by Two');
});

test('deleting an album cascades to songs', function () {
    $album = Album::factory()->create();
    $song  = Song::factory()->create(['album_id' => $album->id]);

    $album->delete();

    $this->assertDatabaseMissing('songs', ['id' => $song->id]);
});
