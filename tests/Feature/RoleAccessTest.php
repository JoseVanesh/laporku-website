<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Tes otoritas route berbasis middleware (role:...) tanpa menyentuh Firestore.
 *
 * Controller yang lolos middleware akan query Firestore, jadi tes ini hanya
 * mencakup kasus yang DITOLAK middleware atau gagal validasi sebelum query.
 */
class RoleAccessTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private function sessionFor(string $role): array
    {
        return [
            'firebase.uid' => 'uji-uid',
            'firebase.role' => $role,
            'firebase.name' => 'Pengguna Uji',
            'firebase.email' => 'uji@example.com',
            'firebase.blok' => 'A',
        ];
    }

    public function test_guest_is_redirected_to_login_from_protected_pages(): void
    {
        $this->get('/pengumuman')->assertRedirect(route('login'));
        $this->get('/admin/pengumuman')->assertRedirect(route('login'));
        $this->get('/admin/pengguna')->assertRedirect(route('login'));
    }

    public function test_warga_is_forbidden_from_pengurus_pages(): void
    {
        $session = $this->sessionFor('warga');

        foreach (['/admin/laporan', '/admin/pengguna', '/admin/kategori', '/admin/pengumuman'] as $path) {
            $this->withSession($session)->get($path)->assertForbidden();
        }

        $this->withSession($session)
            ->post('/admin/pengumuman', ['judul' => 'Judul uji', 'isi' => 'Isi pengumuman uji.'])
            ->assertForbidden();
    }

    public function test_rt_is_forbidden_from_rw_only_pages(): void
    {
        $session = $this->sessionFor('rt');

        foreach (['/admin/pengguna', '/admin/kategori', '/admin/pengumuman'] as $path) {
            $this->withSession($session)->get($path)->assertForbidden();
        }

        $this->withSession($session)->delete('/admin/pengumuman/kosong-id')->assertForbidden();
        $this->withSession($session)->get('/admin/pengumuman/kosong-id/edit')->assertForbidden();
    }

    public function test_pengumuman_store_rejects_empty_payload_with_indonesian_messages(): void
    {
        $this->withSession($this->sessionFor('rw'))
            ->post('/admin/pengumuman', [])
            ->assertRedirect()
            ->assertSessionHasErrors([
                'judul' => 'Judul pengumuman wajib diisi.',
                'isi' => 'Isi pengumuman wajib diisi.',
            ]);
    }

    public function test_stale_admin_session_is_forbidden_until_relogin(): void
    {
        $session = $this->sessionFor('admin');

        $this->withSession($session)->get('/admin/pengguna')->assertForbidden();
        $this->withSession($session)->get('/admin/pengumuman')->assertForbidden();
    }
}
