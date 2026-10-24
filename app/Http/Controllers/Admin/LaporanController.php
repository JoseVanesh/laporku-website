<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LaporanController as WargaLaporanController;
use App\Notifications\StatusLaporanBerubah;
use App\Services\Firestore\CategoryStore;
use App\Services\Firestore\ReportStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class LaporanController extends Controller
{
    public function __construct(
        private readonly ReportStore $reports,
        private readonly CategoryStore $categories,
    ) {}

    public function index(Request $request)
    {
        $role = session('firebase.role');
        $page = max(1, (int) $request->input('page', 1));

        $status = $request->string('status')->toString();
        $kategori = $request->string('kategori')->toString();
        $blokInput = $request->string('blok')->toString();

        $filters = [];

        if ($role === 'rt') {
            $filters['blok'] = session('firebase.blok', '');
        } elseif ($blokInput !== '') {
            $filters['blok'] = $blokInput;
        }

        if (in_array($status, array_keys(WargaLaporanController::STATUS_LABELS), true)) {
            $filters['status'] = $status;
        }

        if ($kategori !== '' && in_array($kategori, $this->categories->names(), true)) {
            $filters['kategori'] = $kategori;
        }

        $result = $this->reports->paginate($filters, 15, ($page - 1) * 15);

        return view('admin.laporan.index', [
            'laporan' => $result['items'],
            'hasMore' => $result['has_more'],
            'page' => $page,
            'filters' => [
                'status' => $filters['status'] ?? null,
                'kategori' => $filters['kategori'] ?? null,
                'blok' => $filters['blok'] ?? null,
            ],
            'statusLabels' => WargaLaporanController::STATUS_LABELS,
            'kategoriList' => $this->categories->all(),
            'blokTerbatas' => $role === 'rt',
            'blokKosong' => $role === 'rt' && session('firebase.blok', '') === '',
        ]);
    }

    public function show(string $id)
    {
        $laporan = $this->reports->find($id);

        abort_if($laporan === null, 404);

        $this->authorizeReport($laporan);

        return view('admin.laporan.show', [
            'laporan' => $laporan,
            'komentar' => $this->reports->comments($id),
            'statusLabels' => WargaLaporanController::STATUS_LABELS,
        ]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $laporan = $this->reports->find($id);

        abort_if($laporan === null, 404);

        $this->authorizeReport($laporan);

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(WargaLaporanController::STATUS_LABELS)),
        ], [
            'status.required' => 'Pilih status laporan.',
            'status.in' => 'Status laporan tidak valid.',
        ]);

        $statusBaru = $validated['status'];

        $this->reports->update($id, [
            'status' => $statusBaru,
            'updated_at' => now()->toIso8601String(),
        ]);

        $this->reports->addComment($id, [
            'uid' => session('firebase.uid'),
            'nama' => session('firebase.name'),
            'peran' => session('firebase.role'),
            'isi' => 'Status diubah menjadi "'.WargaLaporanController::STATUS_LABELS[$statusBaru].'".',
            'sistem' => true,
            'created_at' => now()->toIso8601String(),
        ]);

        $email = (string) ($laporan['email_penulis'] ?? '');

        if ($email !== '' && $statusBaru !== ($laporan['status'] ?? '')) {
            try {
                Notification::route('mail', $email)->notify(
                    new StatusLaporanBerubah($laporan, $statusBaru, session('firebase.name', '-'))
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Status laporan diperbarui menjadi "'.WargaLaporanController::STATUS_LABELS[$statusBaru].'".');
    }

    public function comment(Request $request, string $id)
    {
        $laporan = $this->reports->find($id);

        abort_if($laporan === null, 404);

        $this->authorizeReport($laporan);

        $validated = $request->validate([
            'isi' => 'required|string|min:2|max:1000',
        ], [
            'isi.required' => 'Tulis komentar terlebih dahulu.',
        ]);

        $this->reports->addComment($id, [
            'uid' => session('firebase.uid'),
            'nama' => session('firebase.name'),
            'peran' => session('firebase.role'),
            'isi' => $validated['isi'],
            'created_at' => now()->toIso8601String(),
        ]);

        return back()->with('success', 'Komentar dikirim ke pemilik laporan.');
    }

    /**
     * RT hanya boleh mengakses laporan dari bloknya sendiri.
     *
     * @param  array<string, mixed>  $laporan
     */
    private function authorizeReport(array $laporan): void
    {
        if (session('firebase.role') === 'rt') {
            abort_unless(($laporan['blok'] ?? '') === session('firebase.blok'), 403);
        }
    }
}
