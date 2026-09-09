<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'tenant_id' => Tenant::factory(),
            'primary_category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->optional()->sentence(),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'is_active' => true,
            'is_featured' => false,
            'published_at' => now(),
            'meta_title' => Str::limit($title, 60, ''),
            'meta_description' => fake()->sentence(),
            'is_indexable' => true,
            'canonical_url' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Article $article): void {
            if ($article->categories()->exists()) {
                return;
            }

            $article->categories()->attach($article->primary_category_id);
        });
    }

    public function featured(): static
    {
        return $this->state(fn (): array => [
            'is_featured' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
