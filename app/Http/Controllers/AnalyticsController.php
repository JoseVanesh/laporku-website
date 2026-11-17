<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BlockScope;
use App\Services\Firestore\CategoryStore;
use App\Services\Firestore\ReportStore;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly ReportStore $reports,
        private readonly CategoryStore $categories,
        private readonly BlockScope $scope,
    ) {}

    public function index(Request $request)
    {
        $role = $this->scope->role();
        $days = (int) $request->input('days', 30);
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;

        $filters = $this->scope->reportFilters();
        $blok = trim($request->string('blok')->toString());

        if ($role === 'rw') {
            if ($blok !== '' && $this->scope->canAccessBlock($blok)) {
                $filters['blok'] = $blok;
            }
        } elseif ($role === 'super_admin' && $blok !== '') {
            $filters['blok'] = $blok;
        }

        $status = $request->string('status')->toString();

        if (in_array($status, LaporanController::STATUSES, true)) {
            $filters['status'] = $status;
        }

        $kategori = trim($request->string('kategori')->toString());
        $kategoriList = $this->categories->names();

        if ($kategori !== '' && in_array($kategori, $kategoriList, true)) {
            $filters['kategori'] = $kategori;
        }

        $analytics = $this->reports->analytics($filters, $days);

        if ($role === 'rw') {
            $blocks = array_map(
                static fn (string $managedBlock): array => ['blok' => $managedBlock],
                $this->scope->managedBlocks(),
            );
        } elseif ($role === 'super_admin') {
            $blocks = $this->reports->blokSummary([])['bloks'];
        } else {
            $blocks = [];
        }

        $selectedBlock = $filters['blok'] ?? null;

        return view('analisis.index', [
            'role' => $role,
            'days' => $days,
            'filters' => [
                'blok' => is_string($selectedBlock) ? $selectedBlock : null,
                'status' => $filters['status'] ?? null,
                'kategori' => $filters['kategori'] ?? null,
            ],
            'analytics' => $analytics,
            'blocks' => $blocks,
            'kategoriList' => $kategoriList,
            'blokLabel' => $this->scope->label(),
            'statusLabels' => LaporanController::STATUS_LABELS,
        ]);
    }
}
