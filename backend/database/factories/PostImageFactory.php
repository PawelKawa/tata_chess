<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostImageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'path'    => 'galleries/' . $this->faker->uuid() . '.jpg',
            'order'   => 0,
        ];
    }
}
