<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\AnnouncementStore;
use Illuminate\Http\Request;

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

        $this->announcements->create([
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'penulis_uid' => (string) session('firebase.uid'),
            'penulis_nama' => (string) session('firebase.name', '-'),
            'created_at' => now()->toIso8601String(),
        ]);

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

        $this->announcements->update($id, [
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'updated_at' => now()->toIso8601String(),
        ]);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(string $id)
    {
        $this->announcements->delete($id);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman dihapus.');
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
