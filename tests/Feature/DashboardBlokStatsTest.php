<?php

namespace Tests\Feature;

use App\Services\Firestore\AnnouncementStore;
use App\Services\Firestore\ReportStore;
use Tests\TestCase;

class DashboardBlokStatsTest extends TestCase
{
    public function test_rk_dashboard_passes_all_blocks_for_rw(): void
    {
        $blokStats = ['bloks' => [['blok' => 'A', 'total' => 2], ['blok' => 'B', 'total' => 1]], 'total' => 3];

        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('blokSummary')->once()->with([])->andReturn($blokStats);
        $reports->shouldReceive('summary')->once()->with([])->andReturn([
            'total' => 3,
            'by_status' => ['baru' => 2, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 1],
            'with_location' => 0,
        ]);
        $reports->shouldReceive('paginate')->once()->with([], 5, 0)->andReturn(['items' => [], 'has_more' => false]);

        $announcements = $this->mock(AnnouncementStore::class);
        $announcements->shouldReceive('latest')->once()->with(3)->andReturn([]);

        $this->withSession([
            'firebase.uid' => 'rw-uid',
            'firebase.role' => 'rw',
            'firebase.name' => 'Pengurus RW',
            'firebase.email' => 'rw@example.com',
            'firebase.blok' => '',
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
        $reports->shouldReceive('summary')->once()->with(['blok' => 'A'])->andReturn([
            'total' => 1,
            'by_status' => ['baru' => 1, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0],
            'with_location' => 0,
        ]);
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
}
