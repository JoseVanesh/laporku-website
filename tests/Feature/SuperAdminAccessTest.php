<?php

namespace Tests\Feature;

use App\Services\Firestore\CategoryStore;
use App\Services\Firestore\UserStore;
use Tests\TestCase;

class SuperAdminAccessTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    private function sessionFor(string $role, string $uid = 'viewer-uid'): array
    {
        return [
            'firebase.uid' => $uid,
            'firebase.role' => $role,
            'firebase.name' => 'Pengguna Uji',
            'firebase.email' => $role.'@example.com',
            'firebase.blok' => '',
        ];
    }

    public function test_super_admin_can_open_rw_only_pages(): void
    {
        $users = $this->mock(UserStore::class);
        $users->shouldReceive('paginate')->once()->with([], 20, 0)->andReturn(['items' => [], 'has_more' => false]);

        $this->withSession($this->sessionFor('super_admin'))
            ->get('/admin/pengguna')
            ->assertOk();

        $this->mock(CategoryStore::class)
            ->shouldReceive('all')
            ->once()
            ->andReturn([]);

        $this->withSession($this->sessionFor('super_admin'))
            ->get('/admin/kategori')
            ->assertOk();
    }

    public function test_super_admin_can_change_rt_to_rw_and_move_block(): void
    {
        $users = $this->mock(UserStore::class);
        $users->shouldReceive('find')->once()->with('target-uid')->andReturn([
            'uid' => 'target-uid',
            'nama' => 'Ketua RT A',
            'telepon' => '08123456789',
            'role' => 'rt',
            'blok' => 'A',
            'rumah' => '',
        ]);
        $users->shouldReceive('update')->once()->with('target-uid', [
            'nama' => 'Ketua RW B',
            'telepon' => '08123456789',
            'role' => 'rw',
            'blok' => 'B',
            'rumah' => '',
        ]);

        $this->withSession($this->sessionFor('super_admin', 'super-uid'))
            ->put('/admin/pengguna/target-uid', [
                'nama' => 'Ketua RW B',
                'telepon' => '08123456789',
                'role' => 'rw',
                'blok' => 'B',
                'rumah' => '',
            ])
            ->assertRedirect(route('admin.users.index'));
    }

    public function test_rw_cannot_promote_user_to_super_admin(): void
    {
        $users = $this->mock(UserStore::class);
        $users->shouldReceive('find')->once()->with('target-uid')->andReturn([
            'uid' => 'target-uid',
            'nama' => 'Warga Uji',
            'telepon' => '08123456789',
            'role' => 'warga',
            'blok' => 'A',
            'rumah' => '',
        ]);
        $users->shouldNotReceive('update');

        $this->withSession($this->sessionFor('rw', 'rw-uid'))
            ->put('/admin/pengguna/target-uid', [
                'nama' => 'Warga Uji',
                'telepon' => '08123456789',
                'role' => 'super_admin',
                'blok' => 'A',
                'rumah' => '',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('role');
    }

    public function test_rw_cannot_edit_super_admin_account(): void
    {
        $users = $this->mock(UserStore::class);
        $users->shouldReceive('find')->once()->with('super-uid')->andReturn([
            'uid' => 'super-uid',
            'nama' => 'Super Admin',
            'role' => 'super_admin',
        ]);

        $this->withSession($this->sessionFor('rw', 'rw-uid'))
            ->get('/admin/pengguna/super-uid/edit')
            ->assertForbidden();
    }
}
