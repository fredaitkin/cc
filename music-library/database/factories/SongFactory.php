<?php

namespace Database\Factories;

use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

class SongFactory extends Factory
{
    public function definition(): array
    {
        $minutes = $this->faker->numberBetween(1, 7);
        $seconds = str_pad($this->faker->numberBetween(0, 59), 2, '0', STR_PAD_LEFT);

        return [
            'title'        => $this->faker->sentence(3),
            'duration'     => "$minutes:$seconds",
            'genre'        => $this->faker->optional()->randomElement(['Rock', 'Pop', 'Jazz', 'Classical', 'Hip-Hop']),
            'release_year' => $this->faker->optional()->numberBetween(1960, 2024),
            'album_id'     => Album::factory(),
        ];
    }
}
