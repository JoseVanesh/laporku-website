<?php

namespace Tests\Feature;

use App\Services\Firestore\ReportStore;
use Tests\TestCase;

class BlockScopeAccessTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function report(string $block): array
    {
        return [
            'id' => 'laporan-uji',
            'kategori' => 'Keamanan',
            'status' => 'baru',
            'judul' => 'Laporan uji',
            'deskripsi' => 'Isi laporan uji.',
            'nama_penulis' => 'Warga Uji',
            'email_penulis' => 'warga@example.com',
            'blok' => $block,
            'rumah' => '01',
            'created_at' => now()->toIso8601String(),
            'media' => [],
        ];
    }

    /**
     * @param  array<int, string>  $blocks
     * @return array<string, mixed>
     */
    private function sessionFor(string $role, string $block, array $blocks = []): array
    {
        return [
            'firebase.uid' => $role.'-uid',
            'firebase.role' => $role,
            'firebase.name' => 'Pengguna Uji',
            'firebase.email' => $role.'@example.com',
            'firebase.blok' => $block,
            'firebase.blocks' => $blocks,
        ];
    }

    public function test_rw_can_view_report_in_managed_block(): void
    {
        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('find')->once()->with('laporan-uji')->andReturn($this->report('B'));
        $reports->shouldReceive('comments')->once()->with('laporan-uji')->andReturn([]);

        $this->withSession($this->sessionFor('rw', 'A', ['A', 'B']))
            ->get('/admin/laporan/laporan-uji')
            ->assertOk();
    }

    public function test_rw_cannot_view_report_outside_managed_blocks(): void
    {
        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('find')->once()->with('laporan-uji')->andReturn($this->report('F'));
        $reports->shouldNotReceive('comments');

        $this->withSession($this->sessionFor('rw', 'A', ['A', 'B']))
            ->get('/admin/laporan/laporan-uji')
            ->assertForbidden();
    }

    public function test_non_warga_cannot_fetch_dashboard_map_points(): void
    {
        $this->mock(ReportStore::class);

        $this->withSession($this->sessionFor('rt', 'A'))
            ->get('/dashboard/points')
            ->assertForbidden();
    }
}
