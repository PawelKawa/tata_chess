<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'title'        => $title,
            'slug'         => Str::slug($title),
            'content'      => '<p>' . implode('</p><p>', $this->faker->paragraphs(3)) . '</p>',
            'cover_image'  => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(['published_at' => now()]);
    }
}
