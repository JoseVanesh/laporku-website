<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Firestore\ReportStore;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly ReportStore $reports) {}

    public function index()
    {
        $role = (string) session('firebase.role');
        $isStaff = in_array($role, ['admin', 'rt'], true);

        $scopeFilters = $isStaff
            ? ($role === 'rt' ? ['blok' => (string) session('firebase.blok', '')] : [])
            : ['uid_penulis' => (string) session('firebase.uid')];

        return view('dashboard.index', [
            'role' => $role,
            'isStaff' => $isStaff,
            'summary' => $this->reports->summary($scopeFilters),
            'latest' => $this->reports->paginate($scopeFilters, 5, 0)['items'],
            'statusLabels' => LaporanController::STATUS_LABELS,
            'blokKosong' => $role === 'rt' && (string) session('firebase.blok', '') === '',
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
        $isStaff = in_array($role, ['admin', 'rt'], true);

        $filters = [];

        if ($role === 'rt') {
            $filters['blok'] = (string) session('firebase.blok', '');
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
