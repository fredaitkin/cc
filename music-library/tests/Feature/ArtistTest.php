<?php

use App\Models\Artist;
use App\Models\User;

test('guest can view artists index', function () {
    Artist::factory()->create(['name' => 'Test Artist']);

    $response = $this->get(route('artists.index'));

    $response->assertOk()->assertSee('Test Artist');
});

test('guest can view artist detail', function () {
    $artist = Artist::factory()->create(['name' => 'Detail Artist', 'bio' => 'A great bio.']);

    $response = $this->get(route('artists.show', $artist));

    $response->assertOk()->assertSee('Detail Artist')->assertSee('A great bio.');
});

test('guest cannot add an artist', function () {
    $response = $this->post(route('artists.store'), ['name' => 'New Artist']);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseMissing('artists', ['name' => 'New Artist']);
});

test('guest cannot remove an artist', function () {
    $artist = Artist::factory()->create();

    $response = $this->delete(route('artists.destroy', $artist));

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('artists', ['id' => $artist->id]);
});

test('admin can add an artist', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->post(route('artists.store'), [
        'name' => 'New Artist',
        'bio'  => 'Some bio text.',
    ]);

    $response->assertRedirect(route('artists.index'));
    $this->assertDatabaseHas('artists', ['name' => 'New Artist']);
});

test('admin can remove an artist', function () {
    $admin  = User::factory()->create();
    $artist = Artist::factory()->create();

    $response = $this->actingAs($admin)->delete(route('artists.destroy', $artist));

    $response->assertRedirect(route('artists.index'));
    $this->assertDatabaseMissing('artists', ['id' => $artist->id]);
});

test('artists can be searched by name', function () {
    Artist::factory()->create(['name' => 'Radiohead']);
    Artist::factory()->create(['name' => 'Coldplay']);

    $response = $this->get(route('artists.index', ['q' => 'Radio']));

    $response->assertOk()->assertSee('Radiohead')->assertDontSee('Coldplay');
});
