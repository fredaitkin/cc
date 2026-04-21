<?php

use App\Models\Album;
use App\Models\Artist;
use App\Models\User;

test('guest can view albums index', function () {
    $album = Album::factory()->create(['name' => 'Test Album']);

    $response = $this->get(route('albums.index'));

    $response->assertOk()->assertSee('Test Album');
});

test('guest can view album detail', function () {
    $album = Album::factory()->create(['name' => 'Detail Album']);

    $response = $this->get(route('albums.show', $album));

    $response->assertOk()->assertSee('Detail Album');
});

test('guest cannot add an album', function () {
    $artist = Artist::factory()->create();

    $response = $this->post(route('albums.store'), [
        'name'      => 'New Album',
        'artist_id' => $artist->id,
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseMissing('albums', ['name' => 'New Album']);
});

test('guest cannot remove an album', function () {
    $album = Album::factory()->create();

    $response = $this->delete(route('albums.destroy', $album));

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('albums', ['id' => $album->id]);
});

test('admin can add an album', function () {
    $admin  = User::factory()->create();
    $artist = Artist::factory()->create();

    $response = $this->actingAs($admin)->post(route('albums.store'), [
        'name'         => 'New Album',
        'artist_id'    => $artist->id,
        'release_year' => 2023,
    ]);

    $response->assertRedirect(route('albums.index'));
    $this->assertDatabaseHas('albums', ['name' => 'New Album', 'artist_id' => $artist->id]);
});

test('admin can remove an album', function () {
    $admin = User::factory()->create();
    $album = Album::factory()->create();

    $response = $this->actingAs($admin)->delete(route('albums.destroy', $album));

    $response->assertRedirect(route('albums.index'));
    $this->assertDatabaseMissing('albums', ['id' => $album->id]);
});

test('deleting an artist cascades to albums', function () {
    $artist = Artist::factory()->create();
    $album  = Album::factory()->create(['artist_id' => $artist->id]);

    $artist->delete();

    $this->assertDatabaseMissing('albums', ['id' => $album->id]);
});
