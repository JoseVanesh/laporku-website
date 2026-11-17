<?php

namespace Tests\Feature;

use App\Services\Firestore\AnnouncementStore;
use Mockery;
use Tests\TestCase;

class AnnouncementImageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['laporku.cloudinary.cloud_name' => 'demo']);
    }

    /**
     * @return array<string, string>
     */
    private function sessionFor(string $role = 'rw'): array
    {
        return [
            'firebase.uid' => $role.'-uid',
            'firebase.role' => $role,
            'firebase.name' => 'Pengurus Uji',
            'firebase.email' => $role.'@example.com',
            'firebase.blok' => '',
        ];
    }

    public function test_announcement_with_valid_cloudinary_cover_is_saved(): void
    {
        $announcements = $this->mock(AnnouncementStore::class);
        $announcements->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                return ($payload['judul'] ?? '') === 'Kerja Bakti'
                    && ($payload['gambar']['url'] ?? '') === 'https://res.cloudinary.com/demo/image/upload/pengumuman/cover.jpg'
                    && ($payload['gambar']['mime'] ?? '') === 'image/jpeg'
                    && ($payload['gambar']['size'] ?? 0) === 200000;
            }));

        $this->withSession($this->sessionFor())
            ->post('/admin/pengumuman', [
                'judul' => 'Kerja Bakti',
                'isi' => 'Kerja bakti akan dilaksanakan hari Minggu pagi.',
                'gambar_url' => 'https://res.cloudinary.com/demo/image/upload/pengumuman/cover.jpg',
                'gambar_path' => 'pengumuman/cover',
                'gambar_mime' => 'image/jpeg',
                'gambar_size' => '200000',
            ])
            ->assertRedirect(route('admin.pengumuman.index'));
    }

    public function test_announcement_rejects_cover_from_untrusted_host(): void
    {
        $announcements = $this->mock(AnnouncementStore::class);
        $announcements->shouldNotReceive('create');

        $this->withSession($this->sessionFor())
            ->post('/admin/pengumuman', [
                'judul' => 'Kerja Bakti',
                'isi' => 'Kerja bakti akan dilaksanakan hari Minggu pagi.',
                'gambar_url' => 'https://example.com/cover.jpg',
                'gambar_path' => 'cover',
                'gambar_mime' => 'image/jpeg',
                'gambar_size' => '200000',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('gambar_url');
    }

    public function test_removing_announcement_cover_updates_document(): void
    {
        $announcements = $this->mock(AnnouncementStore::class);
        $announcements->shouldReceive('find')->once()->with('announcement-1')->andReturn([
            'id' => 'announcement-1',
            'judul' => 'Lama',
            'isi' => 'Isi lama pengumuman.',
            'gambar' => ['url' => 'https://res.cloudinary.com/demo/image/upload/lama.jpg'],
        ]);
        $announcements->shouldReceive('update')
            ->once()
            ->with('announcement-1', Mockery::on(fn (array $payload): bool => array_key_exists('gambar', $payload) && $payload['gambar'] === null));

        $this->withSession($this->sessionFor())
            ->put('/admin/pengumuman/announcement-1', [
                'judul' => 'Baru',
                'isi' => 'Isi pengumuman yang baru.',
                'hapus_gambar' => '1',
            ])
            ->assertRedirect(route('admin.pengumuman.index'));
    }
}
