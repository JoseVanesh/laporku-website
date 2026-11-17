<?php

namespace Tests\Feature;

use App\Services\Firestore\AnnouncementStore;
use App\Services\Firestore\ReportStore;
use App\Services\Firestore\UserStore;
use Tests\TestCase;

class DashboardBlokStatsTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function analyticsPayload(int $total = 3): array
    {
        return [
            'total' => $total,
            'by_status' => ['baru' => 2, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 1],
            'by_kategori' => [],
            'by_blok' => [],
            'daily' => [
                ['tanggal' => now()->format('Y-m-d'), 'label' => now()->format('d M'), 'total' => $total],
            ],
            'by_hari' => [],
            'by_jam' => [],
            'peak_hour' => null,
            'completion_rate' => 0.0,
            'avg_per_day' => 0.43,
            'active_days' => 1,
        ];
    }

    public function test_rw_dashboard_is_scoped_to_managed_blocks(): void
    {
        $blokStats = ['bloks' => [['blok' => 'A', 'total' => 2], ['blok' => 'B', 'total' => 1]], 'total' => 3];
        $filters = ['blok' => ['A', 'B']];

        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('blokSummary')->once()->with($filters)->andReturn($blokStats);
        $reports->shouldReceive('analytics')->once()->with($filters, 7)->andReturn($this->analyticsPayload());
        $reports->shouldReceive('summary')->once()->with($filters)->andReturn([
            'total' => 3,
            'by_status' => ['baru' => 2, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 1],
            'with_location' => 0,
        ]);
        $reports->shouldReceive('paginate')->once()->with($filters + ['status' => 'baru'], 5, 0)->andReturn(['items' => [], 'has_more' => false]);
        $reports->shouldReceive('paginate')->once()->with($filters, 5, 0)->andReturn(['items' => [], 'has_more' => false]);

        $announcements = $this->mock(AnnouncementStore::class);
        $announcements->shouldReceive('latest')->once()->with(3)->andReturn([]);

        $this->withSession([
            'firebase.uid' => 'rw-uid',
            'firebase.role' => 'rw',
            'firebase.name' => 'Pengurus RW',
            'firebase.email' => 'rw@example.com',
            'firebase.blok' => 'A',
            'firebase.blocks' => ['A', 'B'],
        ])
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('blokStats', $blokStats);
    }

    public function test_rk_dashboard_scopes_blok_stats_to_own_block_for_rt(): void
    {
        $blokStats = ['bloks' => [['blok' => 'A', 'total' => 1]], 'total' => 1];

        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('blokSummary')->once()->with(['blok' => 'A'])->andReturn($blokStats);
        $reports->shouldReceive('analytics')->once()->with(['blok' => 'A'], 7)->andReturn($this->analyticsPayload(1));
        $reports->shouldReceive('summary')->once()->with(['blok' => 'A'])->andReturn([
            'total' => 1,
            'by_status' => ['baru' => 1, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0],
            'with_location' => 0,
        ]);
        $reports->shouldReceive('paginate')->once()->with(['blok' => 'A', 'status' => 'baru'], 5, 0)->andReturn(['items' => [], 'has_more' => false]);
        $reports->shouldReceive('paginate')->once()->with(['blok' => 'A'], 5, 0)->andReturn(['items' => [], 'has_more' => false]);

        $announcements = $this->mock(AnnouncementStore::class);
        $announcements->shouldReceive('latest')->once()->with(3)->andReturn([]);

        $this->withSession([
            'firebase.uid' => 'rt-uid',
            'firebase.role' => 'rt',
            'firebase.name' => 'Ketua RT',
            'firebase.email' => 'rt@example.com',
            'firebase.blok' => 'A',
        ])
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('blokStats', $blokStats);
    }

    public function test_super_admin_dashboard_shows_control_center_summary(): void
    {
        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('blokSummary')->once()->with([])->andReturn(['bloks' => [], 'total' => 0]);
        $reports->shouldReceive('analytics')->once()->with([], 7)->andReturn($this->analyticsPayload(0));
        $reports->shouldReceive('summary')->once()->with([])->andReturn([
            'total' => 0,
            'by_status' => ['baru' => 0, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0],
            'with_location' => 0,
        ]);
        $reports->shouldReceive('paginate')->once()->with(['status' => 'baru'], 5, 0)->andReturn(['items' => [], 'has_more' => false]);
        $reports->shouldReceive('paginate')->once()->with([], 5, 0)->andReturn(['items' => [], 'has_more' => false]);

        $announcements = $this->mock(AnnouncementStore::class);
        $announcements->shouldReceive('latest')->once()->with(3)->andReturn([]);

        $users = $this->mock(UserStore::class);
        $users->shouldReceive('summary')->once()->andReturn([
            'total' => 4,
            'by_role' => ['warga' => 2, 'rt' => 1, 'rw' => 0, 'super_admin' => 1],
            'blocks' => ['A', 'B'],
        ]);

        $this->withSession([
            'firebase.uid' => 'super-uid',
            'firebase.role' => 'super_admin',
            'firebase.name' => 'Super Admin',
            'firebase.email' => 'super@example.com',
            'firebase.blok' => '',
        ])
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('isSuperAdmin', true)
            ->assertSee('Pusat Kendali Super Admin');
    }
}
