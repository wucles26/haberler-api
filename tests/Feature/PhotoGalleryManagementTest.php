<?php

namespace Tests\Feature;

use App\Enums\TenantRole;
use App\Livewire\Tenants\ManagePhotoGalleries;
use App\Models\GalleryPhoto;
use App\Models\PhotoGallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\CreatesTenants;
use Tests\TestCase;

class PhotoGalleryManagementTest extends TestCase
{
    use CreatesTenants;
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_admin_can_create_gallery_with_multiple_photos(): void
    {
        Storage::fake('public');
        [$admin, $tenant] = $this->createUserWithTenant();

        Livewire::actingAs($admin)
            ->test(ManagePhotoGalleries::class, ['tenant' => $tenant])
            ->set('title', 'Şehir fotoğrafları')
            ->set('cover', UploadedFile::fake()->image('cover.jpg'))
            ->set('photos', [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
            ])
            ->call('saveGallery')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('photo_galleries', [
            'tenant_id' => $tenant->id,
            'slug' => 'sehir-fotograflari',
        ]);
        $this->assertDatabaseCount('gallery_photos', 2);
    }

    public function test_author_cannot_manage_photo_galleries(): void
    {
        [$author, $tenant] = $this->createUserWithTenant(TenantRole::Author);

        $this->actingAs($author)
            ->get(route('tenant.photo-galleries', $tenant))
            ->assertForbidden();
    }

    public function test_gallery_rejects_invalid_uploads(): void
    {
        [$admin, $tenant] = $this->createUserWithTenant();

        Livewire::actingAs($admin)
            ->test(ManagePhotoGalleries::class, ['tenant' => $tenant])
            ->set('title', 'Galeri')
            ->set('photos', [UploadedFile::fake()->create('malware.pdf', 100)])
            ->call('saveGallery')
            ->assertHasErrors(['photos.0']);
    }

    public function test_editor_can_update_photo_order_and_status(): void
    {
        [$editor, $tenant] = $this->createUserWithTenant(TenantRole::Editor);
        $gallery = PhotoGallery::factory()->for($tenant)->create();
        $photo = GalleryPhoto::factory()->for($gallery, 'gallery')->create();

        Livewire::actingAs($editor)
            ->test(ManagePhotoGalleries::class, ['tenant' => $tenant])
            ->call('updatePhotoOrder', $photo->id, 4)
            ->call('togglePhotoActive', $photo->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('gallery_photos', [
            'id' => $photo->id,
            'sort_order' => 4,
            'is_active' => false,
        ]);
    }
}
