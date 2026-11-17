<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Firestore\CategoryStore;
use App\Services\Firestore\ReportStore;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public const STATUSES = ['baru', 'diproses', 'selesai', 'ditolak'];

    public const STATUS_LABELS = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    public function __construct(
        private readonly ReportStore $reports,
        private readonly CategoryStore $categories,
    ) {}

    public function index(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $status = $request->string('status')->toString();

        $filters = ['uid_penulis' => session('firebase.uid')];

        if (in_array($status, self::STATUSES, true)) {
            $filters['status'] = $status;
        }

        $result = $this->reports->paginate($filters, 15, ($page - 1) * 15);

        return view('warga.laporan.index', [
            'laporan' => $result['items'],
            'hasMore' => $result['has_more'],
            'page' => $page,
            'statusAktif' => $filters['status'] ?? null,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function create()
    {
        return view('warga.laporan.create', [
            'kategori' => $this->categories->all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|min:5|max:150',
            'kategori' => 'required|string|max:50',
            'deskripsi' => 'required|string|min:10|max:3000',
            'lokasi' => 'nullable|string|max:200',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'media_items' => 'required|array|min:1|max:5',
            'media_items.*' => 'required|string',
        ], [
            'judul.required' => 'Judul laporan wajib diisi.',
            'judul.min' => 'Judul minimal 5 karakter.',
            'kategori.required' => 'Pilih jenis pelaporan.',
            'deskripsi.required' => 'Uraian kejadian wajib diisi.',
            'deskripsi.min' => 'Uraian minimal 10 karakter.',
            'media_items.required' => 'Lampirkan minimal satu foto atau video.',
            'media_items.min' => 'Lampirkan minimal satu foto atau video.',
            'media_items.max' => 'Maksimal 5 berkas.',
        ]);

        if (! in_array($validated['kategori'], $this->categories->names(), true)) {
            return back()->withErrors(['kategori' => 'Jenis pelaporan tidak ditemukan.'])->withInput();
        }

        $cloudName = (string) config('laporku.cloudinary.cloud_name');
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm', 'video/quicktime'];
        $media = [];

        foreach ($validated['media_items'] as $raw) {
            $item = json_decode($raw, true);

            if (! is_array($item)) {
                return back()->withErrors(['media_items' => 'Data lampiran tidak valid. Silakan unggah ulang.'])->withInput();
            }

            $url = (string) ($item['url'] ?? '');
            $mime = (string) ($item['mime'] ?? '');
            $path = (string) ($item['path'] ?? '');
            $size = (int) ($item['size'] ?? 0);
            $tipe = (string) ($item['tipe'] ?? '');

            $host = (string) parse_url($url, PHP_URL_HOST);
            $pathUrl = (string) parse_url($url, PHP_URL_PATH);
            $validHost = $host === 'res.cloudinary.com';
            $validCloud = $cloudName === '' || str_starts_with($pathUrl, '/'.$cloudName.'/');

            if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL) || ! $validHost || ! $validCloud) {
                return back()->withErrors(['media_items' => 'Lampiran harus berasal dari penyimpanan yang sah.'])->withInput();
            }

            if (! in_array($mime, $allowedMimes, true)) {
                return back()->withErrors(['media_items' => 'Format lampiran tidak didukung.'])->withInput();
            }

            if ($size <= 0 || $size > 10 * 1024 * 1024) {
                return back()->withErrors(['media_items' => 'Ukuran tiap berkas maksimal 10 MB.'])->withInput();
            }

            if (! in_array($tipe, ['image', 'video'], true)) {
                $tipe = str_starts_with($mime, 'video/') ? 'video' : 'image';
            }

            $media[] = [
                'path' => $path,
                'url' => $url,
                'mime' => $mime,
                'size' => $size,
                'tipe' => $tipe,
            ];
        }

        $id = $this->reports->create([
            'uid_penulis' => session('firebase.uid'),
            'nama_penulis' => session('firebase.name'),
            'email_penulis' => session('firebase.email', ''),
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi' => (string) ($validated['lokasi'] ?? ''),
            'lat' => isset($validated['lat']) && $validated['lat'] !== null ? (float) $validated['lat'] : null,
            'lng' => isset($validated['lng']) && $validated['lng'] !== null ? (float) $validated['lng'] : null,
            'blok' => session('firebase.blok', ''),
            'status' => 'baru',
            'media' => $media,
            'jmedia' => count($media),
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('laporan.show', $id)
            ->with('success', 'Laporan berhasil dikirim. Terima kasih atas laporannya!');
    }

    public function show(Request $request, string $id)
    {
        $laporan = $this->reports->find($id);

        abort_if($laporan === null, 404);

        $this->authorizeView($laporan);

        return view('warga.laporan.show', [
            'laporan' => $laporan,
            'komentar' => $this->reports->comments($id),
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function comment(Request $request, string $id)
    {
        $laporan = $this->reports->find($id);

        abort_if($laporan === null, 404);

        $this->authorizeView($laporan);

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

        return redirect()->route('laporan.show', $id)->with('success', 'Komentar ditambahkan.');
    }

    /**
     * @param  array<string, mixed>  $laporan
     */
    private function authorizeView(array $laporan): void
    {
        $role = session('firebase.role');
        $isOwner = ($laporan['uid_penulis'] ?? null) === session('firebase.uid');
        $isStaff = in_array($role, ['rw', 'rt', 'super_admin'], true);
        $isSameBlock = ($laporan['blok'] ?? '') === session('firebase.blok');
        $isRtScoped = $role !== 'rt' || $isSameBlock;

        abort_unless($isOwner || ($isStaff && $isRtScoped), 403);
    }
}
