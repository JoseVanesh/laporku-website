<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Firestore\AnnouncementStore;
use App\Services\Firestore\ReportStore;
use App\Services\Firestore\UserStore;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportStore $reports,
        private readonly AnnouncementStore $announcements,
    ) {}

    public function index()
    {
        $role = (string) session('firebase.role');
        $isStaff = in_array($role, ['rw', 'rt', 'super_admin'], true);

        $blok = (string) session('firebase.blok', '');

        $scopeFilters = $isStaff
            ? ($role === 'rt' ? ['blok' => $blok !== '' ? $blok : ReportStore::EMPTY_SCOPE] : [])
            : ['uid_penulis' => (string) session('firebase.uid')];

        $blokStats = $isStaff ? $this->reports->blokSummary($scopeFilters) : ['bloks' => [], 'total' => 0];
        $analytics = $isStaff ? $this->reports->analytics($scopeFilters, 7) : null;
        $perluTindak = $role === 'rt'
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
            'blokKosong' => $role === 'rt' && (string) session('firebase.blok', '') === '',
            'blokStats' => $blokStats,
            'map' => [
                'center' => config('laporku.map.center'),
                'zoom' => config('laporku.map.zoom'),
                'pointsUrl' => route('dashboard.points'),
            ],
        ]);
    }

    public function points(Request $request)
    {
        $role = (string) session('firebase.role');
        $isStaff = in_array($role, ['rw', 'rt', 'super_admin'], true);

        $filters = [];

        if ($role === 'rt') {
            $blok = (string) session('firebase.blok', '');
            $filters['blok'] = $blok !== '' ? $blok : ReportStore::EMPTY_SCOPE;
        }

        $status = $request->string('status')->toString();

        if (in_array($status, LaporanController::STATUSES, true)) {
            $filters['status'] = $status;
        }

        $points = $this->reports->mapPoints($filters);

        $payload = array_map(function (array $item) use ($role, $isStaff): array {
            $canView = $isStaff
                ? ($role !== 'rt' || ($item['blok'] ?? '') === session('firebase.blok'))
                : ($item['uid_penulis'] ?? null) === session('firebase.uid');

            return [
                'id' => (string) $item['id'],
                'judul' => (string) ($item['judul'] ?? ''),
                'kategori' => (string) ($item['kategori'] ?? ''),
                'status' => (string) ($item['status'] ?? 'baru'),
                'blok' => (string) ($item['blok'] ?? ''),
                'lat' => (float) $item['lat'],
                'lng' => (float) $item['lng'],
                'created_at' => (string) ($item['created_at'] ?? ''),
                'url' => $canView
                    ? ($isStaff ? route('admin.laporan.show', $item['id']) : route('laporan.show', $item['id']))
                    : null,
            ];
        }, $points);

        return response()->json([
            'points' => $payload,
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
