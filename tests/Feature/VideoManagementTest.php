<?php

namespace Tests\Feature;

use App\Enums\TenantRole;
use App\Livewire\Tenants\ManageVideos;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class VideoManagementTest extends TestCase
{
    use CreatesTenants;
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_admin_can_create_video_and_generate_embed_code(): void
    {
        Storage::fake('public');
        [$admin, $tenant] = $this->createUserWithTenant();

        Livewire::actingAs($admin)
            ->test(ManageVideos::class, ['tenant' => $tenant])
            ->set('title', 'Tanıtım videosu')
            ->set('video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->set('thumbnail', UploadedFile::fake()->image('thumbnail.jpg'))
            ->call('saveVideo')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('videos', [
            'tenant_id' => $tenant->id,
            'slug' => 'tanitim-videosu',
        ]);
        $this->assertStringContainsString('youtube.com/embed/dQw4w9WgXcQ', (string) Video::query()->firstOrFail()->embed_code);
    }

    public function test_video_rejects_unsupported_url(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();

        Livewire::actingAs($admin)
            ->test(ManageVideos::class, ['tenant' => $tenant])
            ->set('title', 'Geçersiz video')
            ->set('video_url', 'https://example.com/video')
            ->call('saveVideo')
            ->assertStatus(422);
    }

    public function test_author_cannot_manage_videos(): void
    {
        [$author, $tenant] = $this->createUserWithTenant(TenantRole::Author);

        $this->actingAs($author)
            ->get(route('tenant.videos', $tenant))
            ->assertForbidden();
    }
}
