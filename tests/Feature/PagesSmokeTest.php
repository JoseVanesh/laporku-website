<?php

namespace Tests\Feature;

use App\Services\Firestore\UserStore;
use Tests\TestCase;

/**
 * Smoke test yang menembak Firestore sungguhan (butuh service account + composite index).
 *
 * Secara default tes ini DILEWATI. Jalankan dengan:
 *   $env:LAPORKU_LIVE_SMOKE='true'; php artisan test --filter=PagesSmokeTest
 */
class PagesSmokeTest extends TestCase
{
    /**
     * @var array<string, string>
     */
    private array $sessionData = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('LAPORKU_LIVE_SMOKE') !== 'true') {
            $this->markTestSkipped('Butuh Firestore live. Set LAPORKU_LIVE_SMOKE=true untuk menjalankan.');
        }

        $email = (string) getenv('LAPORKU_SMOKE_EMAIL');
        $user = $email !== '' ? app(UserStore::class)->findByEmail($email) : null;

        if ($user === null) {
            $this->markTestSkipped('Pengguna uji tidak ditemukan. Set LAPORKU_SMOKE_EMAIL ke email admin yang sudah terdaftar.');
        }

        $this->sessionData = [
            'firebase.uid' => $user['uid'],
            'firebase.role' => $user['role'] ?? 'admin',
            'firebase.name' => $user['nama'] ?? '',
            'firebase.email' => $user['email'] ?? $email,
            'firebase.blok' => $user['blok'] ?? '',
        ];
    }

    public function test_admin_pages(): void
    {
        $paths = [
            '/dashboard',
            '/admin/laporan',
            '/admin/laporan?status=baru',
            '/admin/laporan?kategori=Keamanan',
            '/admin/laporan?blok=C',
            '/admin/laporan?status=baru&kategori=Keamanan&blok=C',
            '/admin/pengguna',
            '/admin/kategori',
        ];

        foreach ($paths as $path) {
            $this->withSession($this->sessionData)->get($path)->assertOk();
        }

        $this->withSession($this->sessionData)->get('/dashboard/points')->assertOk()->assertJsonStructure(['points', 'server_time']);
    }

    public function test_warga_pages(): void
    {
        $session = ['firebase.role' => 'warga'] + $this->sessionData;

        foreach (['/dashboard', '/laporan', '/laporan?status=baru', '/laporan/baru'] as $path) {
            $this->withSession($session)->get($path)->assertOk();
        }
    }

    public function test_rt_scoped_pages(): void
    {
        $session = ['firebase.role' => 'rt'] + $this->sessionData;

        foreach (['/dashboard', '/admin/laporan', '/admin/laporan?blok=A'] as $path) {
            $this->withSession($session)->get($path)->assertOk();
        }
    }

    public function test_rt_cannot_open_admin_only_pages(): void
    {
        $session = ['firebase.role' => 'rt'] + $this->sessionData;

        $this->withSession($session)->get('/admin/pengguna')->assertForbidden();
        $this->withSession($session)->get('/admin/kategori')->assertForbidden();
    }
}
