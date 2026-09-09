<?php

namespace Database\Factories;

use App\Models\GalleryPhoto;
use App\Models\PhotoGallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryPhoto>
 */
class GalleryPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $path = 'galleries/'.fake()->uuid().'.jpg';

        return [
            'photo_gallery_id' => PhotoGallery::factory(),
            'path' => $path,
            'original_name' => basename($path),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
