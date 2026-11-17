<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\AnnouncementStore;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PengumumanController extends Controller
{
    public function __construct(private readonly AnnouncementStore $announcements) {}

    public function index(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 15;

        $result = $this->announcements->paginate($perPage, ($page - 1) * $perPage);

        return view('admin.pengumuman.index', [
            'pengumuman' => $result['items'],
            'hasMore' => $result['has_more'],
            'page' => $page,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);
        $cover = $this->validatedCover($request);

        $payload = [
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'penulis_uid' => (string) session('firebase.uid'),
            'penulis_nama' => (string) session('firebase.name', '-'),
            'created_at' => now()->toIso8601String(),
        ];

        if ($cover !== null) {
            $payload['gambar'] = $cover;
        }

        $this->announcements->create($payload);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman diterbitkan.');
    }

    public function edit(string $id)
    {
        $item = $this->announcements->find($id);

        abort_if($item === null, 404);

        return view('admin.pengumuman.edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $item = $this->announcements->find($id);

        abort_if($item === null, 404);

        $validated = $this->validatedPayload($request);
        $cover = $this->validatedCover($request);

        $payload = [
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'updated_at' => now()->toIso8601String(),
        ];

        if ($request->boolean('hapus_gambar')) {
            $payload['gambar'] = null;
        } elseif ($cover !== null) {
            $payload['gambar'] = $cover;
        }

        $this->announcements->update($id, $payload);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(string $id)
    {
        $this->announcements->delete($id);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman dihapus.');
    }

    /**
     * @return array{path: string, url: string, mime: string, size: int}|null
     */
    private function validatedCover(Request $request): ?array
    {
        $url = trim((string) $request->input('gambar_url', ''));

        if ($url === '') {
            return null;
        }

        $mime = (string) $request->input('gambar_mime', '');
        $size = (int) $request->input('gambar_size', 0);
        $path = (string) $request->input('gambar_path', '');
        $cloudName = (string) config('laporku.cloudinary.cloud_name');
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        $host = (string) parse_url($url, PHP_URL_HOST);
        $pathUrl = (string) parse_url($url, PHP_URL_PATH);
        $validCloud = $cloudName === '' || str_starts_with($pathUrl, '/'.$cloudName.'/');

        if (! filter_var($url, FILTER_VALIDATE_URL) || $host !== 'res.cloudinary.com' || ! $validCloud) {
            throw ValidationException::withMessages([
                'gambar_url' => 'Gambar harus berasal dari penyimpanan Cloudinary yang sah.',
            ]);
        }

        if (! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'gambar_url' => 'Format gambar harus JPG, PNG, atau WEBP.',
            ]);
        }

        if ($size <= 0 || $size > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'gambar_url' => 'Ukuran gambar maksimal 5 MB.',
            ]);
        }

        return [
            'path' => $path,
            'url' => $url,
            'mime' => $mime,
            'size' => $size,
        ];
    }

    /**
     * @return array{judul: string, isi: string}
     */
    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'judul' => 'required|string|min:3|max:150',
            'isi' => 'required|string|min:5|max:5000',
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'judul.min' => 'Judul pengumuman minimal 3 karakter.',
            'judul.max' => 'Judul pengumuman maksimal 150 karakter.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'isi.min' => 'Isi pengumuman minimal 5 karakter.',
            'isi.max' => 'Isi pengumuman maksimal 5000 karakter.',
        ]);

        return [
            'judul' => trim((string) $validated['judul']),
            'isi' => trim((string) $validated['isi']),
        ];
    }
}
