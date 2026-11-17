<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Firestore\CategoryStore;
use App\Services\Firestore\ReportStore;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly ReportStore $reports,
        private readonly CategoryStore $categories,
    ) {}

    public function index(Request $request)
    {
        $role = (string) session('firebase.role');
        $days = (int) $request->input('days', 30);
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;

        $filters = [];

        if ($role === 'rt') {
            $blok = (string) session('firebase.blok', '');
            $filters['blok'] = $blok !== '' ? $blok : ReportStore::EMPTY_SCOPE;
        } else {
            $blok = trim($request->string('blok')->toString());

            if ($blok !== '') {
                $filters['blok'] = $blok;
            }
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
        $blocks = $role === 'rt' ? [] : $this->reports->blokSummary([])['bloks'];

        return view('analisis.index', [
            'role' => $role,
            'days' => $days,
            'filters' => [
                'blok' => $filters['blok'] ?? null,
                'status' => $filters['status'] ?? null,
                'kategori' => $filters['kategori'] ?? null,
            ],
            'analytics' => $analytics,
            'blocks' => $blocks,
            'kategoriList' => $kategoriList,
            'statusLabels' => LaporanController::STATUS_LABELS,
        ]);
    }
}
