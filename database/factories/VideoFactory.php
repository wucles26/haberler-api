<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'tenant_id' => Tenant::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'embed_code' => '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>',
            'thumbnail_path' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
