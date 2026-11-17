<?php

namespace Tests\Feature;

use App\Services\Firestore\CategoryStore;
use App\Services\Firestore\ReportStore;
use Tests\TestCase;

class AnalyticsAccessTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function analyticsPayload(int $total = 2): array
    {
        return [
            'total' => $total,
            'by_status' => ['baru' => $total, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0],
            'by_kategori' => [['nama' => 'Keamanan', 'total' => $total]],
            'by_blok' => [['blok' => 'A', 'total' => $total, 'baru' => $total, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0]],
            'daily' => [
                ['tanggal' => now()->format('Y-m-d'), 'label' => now()->format('d M'), 'total' => $total],
            ],
            'by_hari' => ['Senin' => $total],
            'by_jam' => array_fill(0, 24, 0),
            'peak_hour' => null,
            'completion_rate' => 0.0,
            'avg_per_day' => 0.29,
            'active_days' => 1,
        ];
    }

    /**
     * @param  array<int, string>  $blocks
     * @return array<string, mixed>
     */
    private function sessionFor(string $role, string $blok = 'A', array $blocks = []): array
    {
        return [
            'firebase.uid' => $role.'-uid',
            'firebase.role' => $role,
            'firebase.name' => 'Pengguna Uji',
            'firebase.email' => $role.'@example.com',
            'firebase.blok' => $blok,
            'firebase.blocks' => $blocks,
        ];
    }

    private function mockCategories(): void
    {
        $categories = $this->mock(CategoryStore::class);
        $categories->shouldReceive('names')->andReturn(['Keamanan', 'Kebersihan']);
    }

    public function test_rt_only_receives_analytics_for_own_block(): void
    {
        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('analytics')
            ->once()
            ->with(['blok' => 'A'], 30)
            ->andReturn($this->analyticsPayload());

        $this->mockCategories();

        $this->withSession($this->sessionFor('rt'))
            ->get('/analisis')
            ->assertOk()
            ->assertViewHas('filters', fn (array $filters): bool => $filters['blok'] === 'A');
    }

    public function test_rt_without_block_does_not_receive_all_reports(): void
    {
        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('analytics')
            ->once()
            ->with(['blok' => ReportStore::EMPTY_SCOPE], 30)
            ->andReturn($this->analyticsPayload(0));

        $this->mockCategories();

        $this->withSession($this->sessionFor('rt', ''))
            ->get('/analisis')
            ->assertOk()
            ->assertViewHas('filters', fn (array $filters): bool => $filters['blok'] === ReportStore::EMPTY_SCOPE);
    }

    public function test_rw_can_filter_analytics_by_managed_block(): void
    {
        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('analytics')
            ->once()
            ->with(['blok' => 'B'], 7)
            ->andReturn($this->analyticsPayload());

        $this->mockCategories();

        $this->withSession($this->sessionFor('rw', 'A', ['A', 'B']))
            ->get('/analisis?days=7&blok=B')
            ->assertOk()
            ->assertViewHas('days', 7)
            ->assertViewHas('blocks', [['blok' => 'A'], ['blok' => 'B']]);
    }

    public function test_rw_cannot_filter_analytics_outside_managed_blocks(): void
    {
        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('analytics')
            ->once()
            ->with(['blok' => ['F', 'G', 'H', 'I', 'J']], 30)
            ->andReturn($this->analyticsPayload());

        $this->mockCategories();

        $this->withSession($this->sessionFor('rw', 'F', ['F', 'G', 'H', 'I', 'J']))
            ->get('/analisis?blok=A')
            ->assertOk()
            ->assertViewHas('filters', fn (array $filters): bool => $filters['blok'] === null);
    }

    public function test_super_admin_can_open_analytics_without_block_filter(): void
    {
        $reports = $this->mock(ReportStore::class);
        $reports->shouldReceive('analytics')->once()->with([], 30)->andReturn($this->analyticsPayload());
        $reports->shouldReceive('blokSummary')->once()->with([])->andReturn(['bloks' => [], 'total' => 0]);

        $this->mockCategories();

        $this->withSession($this->sessionFor('super_admin', ''))
            ->get('/analisis')
            ->assertOk();
    }

    public function test_warga_cannot_open_analytics(): void
    {
        $this->withSession($this->sessionFor('warga'))
            ->get('/analisis')
            ->assertForbidden();
    }
}
