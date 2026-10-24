<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\UserStore;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public const ROLES = ['warga' => 'Warga', 'rt' => 'RT/RW', 'admin' => 'Admin'];

    public function __construct(private readonly UserStore $users) {}

    public function index(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $role = $request->string('role')->toString();

        $filters = in_array($role, array_keys(self::ROLES), true) ? ['role' => $role] : [];

        $result = $this->users->paginate($filters, 20, ($page - 1) * 20);

        return view('admin.users.index', [
            'users' => $result['items'],
            'hasMore' => $result['has_more'],
            'page' => $page,
            'roleAktif' => $filters['role'] ?? null,
            'roles' => self::ROLES,
        ]);
    }

    public function edit(string $uid)
    {
        $user = $this->users->find($uid);

        abort_if($user === null, 404);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, string $uid)
    {
        abort_if($uid === session('firebase.uid'), 403, 'Anda tidak dapat mengubah akun sendiri di sini.');

        $user = $this->users->find($uid);

        abort_if($user === null, 404);

        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:100',
            'telepon' => 'required|string|min:9|max:20',
            'role' => 'required|in:'.implode(',', array_keys(self::ROLES)),
            'blok' => 'nullable|string|max:20',
            'rumah' => 'nullable|string|max:20',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'role.in' => 'Peran tidak valid.',
        ]);

        $blok = (string) ($validated['blok'] ?? '');

        if (in_array($validated['role'], ['rt', 'admin'], true) && $blok === '') {
            return back()->withErrors(['blok' => 'Peran RT/RW dan Admin wajib memiliki blok.'])->withInput();
        }

        $this->users->update($uid, [
            'nama' => $validated['nama'],
            'telepon' => $validated['telepon'],
            'role' => $validated['role'],
            'blok' => $blok,
            'rumah' => (string) ($validated['rumah'] ?? ''),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna "'.$validated['nama'].'" diperbarui. Pengguna perlu login ulang agar peran barunya berlaku.');
    }
}
