<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Firestore\UserStore;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserStore $users) {}

    public function index(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $role = $request->string('role')->toString();
        $roles = $this->roles();

        $filters = in_array($role, array_keys($roles), true) ? ['role' => $role] : [];

        $result = $this->users->paginate($filters, 20, ($page - 1) * 20);

        return view('admin.users.index', [
            'users' => $result['items'],
            'hasMore' => $result['has_more'],
            'page' => $page,
            'roleAktif' => $filters['role'] ?? null,
            'roles' => $roles,
        ]);
    }

    public function edit(string $uid)
    {
        $user = $this->users->find($uid);

        abort_if($user === null, 404);

        $this->authorizeManage($user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'blocks' => (array) config('laporku.blocks', []),
        ]);
    }

    public function update(Request $request, string $uid)
    {
        abort_if($uid === session('firebase.uid'), 403, 'Anda tidak dapat mengubah akun sendiri di sini.');

        $user = $this->users->find($uid);

        abort_if($user === null, 404);

        $this->authorizeManage($user);

        $roles = $this->assignableRoles();

        $blocks = (array) config('laporku.blocks', []);

        $validated = $request->validate([
            'nama' => 'required|string|min:3|max:100',
            'telepon' => 'required|string|min:9|max:20',
            'role' => 'required|in:'.implode(',', array_keys($roles)),
            'blok' => 'nullable|string|max:20',
            'bloks' => 'nullable|array',
            'bloks.*' => 'string|in:'.implode(',', $blocks),
            'rumah' => 'nullable|string|max:20',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'role.in' => 'Peran tidak valid.',
            'bloks.*.in' => 'Blok yang dipilih tidak valid.',
        ]);

        $blok = trim((string) ($validated['blok'] ?? ''));
        $bloks = array_values(array_unique(array_filter(array_map(
            static fn (mixed $block): string => trim((string) $block),
            (array) ($validated['bloks'] ?? []),
        ))));

        if ($validated['role'] === 'rw') {
            if ($bloks === [] && $blok !== '') {
                $bloks = [$blok];
            }

            if ($bloks === []) {
                return back()->withErrors(['bloks' => 'Peran RW wajib memiliki minimal satu blok.'])->withInput();
            }

            if (! in_array($blok, $bloks, true)) {
                $blok = $bloks[0];
            }
        } else {
            $bloks = [];

            if ($validated['role'] === 'rt' && $blok === '') {
                return back()->withErrors(['blok' => 'Peran RT wajib memiliki blok.'])->withInput();
            }
        }

        $this->users->update($uid, [
            'nama' => $validated['nama'],
            'telepon' => $validated['telepon'],
            'role' => $validated['role'],
            'blok' => $blok,
            'bloks' => $bloks,
            'rumah' => (string) ($validated['rumah'] ?? ''),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna "'.$validated['nama'].'" diperbarui. Pengguna perlu login ulang agar peran barunya berlaku.');
    }

    /**
     * @return array<string, string>
     */
    private function roles(): array
    {
        return (array) config('laporku.roles', [
            'warga' => 'Warga',
            'rt' => 'RT',
            'rw' => 'RW',
        ]);
    }

    /**
     * RW tidak boleh mengangkat atau mengubah super admin.
     *
     * @return array<string, string>
     */
    private function assignableRoles(): array
    {
        $roles = $this->roles();

        if (session('firebase.role') !== 'super_admin') {
            unset($roles['super_admin']);
        }

        return $roles;
    }

    /**
     * @param  array<string, mixed>  $user
     */
    private function authorizeManage(array $user): void
    {
        if (($user['role'] ?? 'warga') === 'super_admin' && session('firebase.role') !== 'super_admin') {
            abort(403, 'Hanya super admin yang dapat mengelola akun super admin.');
        }
    }
}
