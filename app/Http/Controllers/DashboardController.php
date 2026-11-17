<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BlockScope;
use App\Services\Firestore\AnnouncementStore;
use App\Services\Firestore\ReportStore;
use App\Services\Firestore\UserStore;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportStore $reports,
        private readonly AnnouncementStore $announcements,
        private readonly BlockScope $scope,
    ) {}

    public function index()
    {
        $role = $this->scope->role();
        $isStaff = $this->scope->isStaff();

        $scopeFilters = $isStaff
            ? $this->scope->reportFilters()
            : ['uid_penulis' => (string) session('firebase.uid')];

        $blokStats = $isStaff ? $this->reports->blokSummary($scopeFilters) : ['bloks' => [], 'total' => 0];
        $analytics = $isStaff ? $this->reports->analytics($scopeFilters, 7) : null;
        $perluTindak = $isStaff
            ? $this->reports->paginate($scopeFilters + ['status' => 'baru'], 5, 0)['items']
            : [];
        $superAdminSummary = $role === 'super_admin' ? app(UserStore::class)->summary() : null;

        return view('dashboard.index', [
            'role' => $role,
            'isStaff' => $isStaff,
            'isSuperAdmin' => $role === 'super_admin',
            'superAdminSummary' => $superAdminSummary,
            'analytics' => $analytics,
            'perluTindak' => $perluTindak,
            'summary' => $this->reports->summary($scopeFilters),
            'latest' => $this->reports->paginate($scopeFilters, 5, 0)['items'],
            'pengumuman' => $this->announcements->latest(3),
            'statusLabels' => LaporanController::STATUS_LABELS,
            'blokKosong' => in_array($role, ['rt', 'rw'], true) && $this->scope->managedBlocks() === [],
            'blokLabel' => $this->scope->label(),
            'blokStats' => $blokStats,
            'map' => $role === 'warga' ? [
                'center' => config('laporku.map.center'),
                'zoom' => config('laporku.map.zoom'),
                'pointsUrl' => route('dashboard.points'),
            ] : null,
        ]);
    }

    public function points(Request $request)
    {
        abort_unless($this->scope->role() === 'warga', 403);

        $filters = [];

        $status = $request->string('status')->toString();

        if (in_array($status, LaporanController::STATUSES, true)) {
            $filters['status'] = $status;
        }

        $points = $this->reports->mapPoints($filters);

        $payload = array_map(function (array $item): array {
            $canView = ($item['uid_penulis'] ?? null) === session('firebase.uid');

            return [
                'id' => (string) $item['id'],
                'judul' => (string) ($item['judul'] ?? ''),
                'kategori' => (string) ($item['kategori'] ?? ''),
                'status' => (string) ($item['status'] ?? 'baru'),
                'blok' => (string) ($item['blok'] ?? ''),
                'lat' => (float) $item['lat'],
                'lng' => (float) $item['lng'],
                'created_at' => (string) ($item['created_at'] ?? ''),
                'url' => $canView ? route('laporan.show', $item['id']) : null,
            ];
        }, $points);

        return response()->json([
            'points' => $payload,
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
