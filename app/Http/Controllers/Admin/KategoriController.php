<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\CategoryStore;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function __construct(private readonly CategoryStore $categories) {}

    public function index()
    {
        return view('admin.kategori.index', [
            'kategori' => $this->categories->all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:50',
        ], [
            'nama.required' => 'Nama jenis pelaporan wajib diisi.',
            'nama.min' => 'Nama jenis pelaporan minimal 3 karakter.',
        ]);

        $nama = trim($validated['nama']);

        foreach ($this->categories->names() as $existing) {
            if (mb_strtolower($existing) === mb_strtolower($nama)) {
                return back()->withErrors(['nama' => 'Jenis pelaporan tersebut sudah ada.'])->withInput();
            }
        }

        $this->categories->create($nama);

        return redirect()->route('admin.kategori.index')->with('success', 'Jenis pelaporan ditambahkan.');
    }

    public function destroy(string $id)
    {
        $this->categories->delete($id);

        return redirect()->route('admin.kategori.index')->with('success', 'Jenis pelaporan dihapus.');
    }
}
