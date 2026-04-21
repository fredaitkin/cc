<?php

namespace Database\Factories;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlbumFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'         => $this->faker->words(3, true),
            'release_year' => $this->faker->optional()->numberBetween(1960, 2024),
            'artist_id'    => Artist::factory(),
        ];
    }
}
