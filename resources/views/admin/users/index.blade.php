@extends('layouts.dashboard')

@section('title', 'Pengguna')
@section('subtitle', 'Kelola peran warga, RT, dan RW')

@section('content')
    <div class="mb-6 space-y-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="q" class="block text-sm font-medium text-slate-700">Cari pengguna</label>
                <input type="search" id="q" name="q" value="{{ $search }}" placeholder="Nama, email, telepon, blok, atau rumah"
                       class="field mt-1.5">
            </div>
            @if ($roleAktif)
                <input type="hidden" name="role" value="{{ $roleAktif }}">
            @endif
            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Cari</button>
                @if ($search !== '' || $roleAktif)
                    <a href="{{ route('admin.users.index') }}" class="btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        <div class="flex flex-wrap items-end justify-between gap-4">
            <p class="text-sm text-slate-500">
                @if ($search !== '')
                    Hasil pencarian untuk "<span class="font-medium text-slate-700">{{ $search }}</span>".
                @else
                    Perubahan peran berlaku setelah pengguna login ulang.
                @endif
            </p>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.index', array_filter(['q' => $search ?: null])) }}"
                   class="chip-toggle {{ $roleAktif === null ? 'is-active' : '' }}">Semua</a>
                @foreach ($roles as $kode => $label)
                    <a href="{{ route('admin.users.index', array_filter(['role' => $kode, 'q' => $search ?: null])) }}"
                       class="chip-toggle {{ $roleAktif === $kode ? 'is-active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if (empty($users))
        <div class="card border-dashed p-10 text-center">
            @if ($search !== '' || $roleAktif)
                <p class="font-medium text-slate-700">Tidak ada pengguna yang cocok dengan pencarian/filter ini.</p>
                <p class="mt-1 text-sm text-slate-500">Coba kata kunci lain atau reset filter.</p>
            @else
                <p class="font-medium text-slate-700">Belum ada pengguna.</p>
            @endif
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-mist-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3">Blok / Rumah</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($users as $user)
                            <tr class="transition hover:bg-brand-50/40">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $user['nama'] ?? '-' }}</p>
                                    @if (($user['uid'] ?? null) === session('firebase.uid'))
                                        <span class="text-xs text-brand-600">(Anda)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-slate-700">{{ $user['email'] ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ $user['telepon'] ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    @php
                                        $blokList = array_values(array_filter((array) ($user['bloks'] ?? [])));
                                        $isRwUser = ($user['role'] ?? '') === 'rw' && $blokList !== [];
                                    @endphp
                                    @if ($isRwUser)
                                        <p>Blok {{ implode(', ', $blokList) }}</p>
                                        <p class="text-xs text-slate-500">Mengampu {{ count($blokList) }} blok</p>
                                    @else
                                        {{ $user['blok'] ?: '-' }} {{ ! empty($user['rumah']) ? '/ ' . $user['rumah'] : '' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-mist-100 px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">{{ $roles[$user['role'] ?? 'warga'] ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $isSelf = ($user['uid'] ?? null) === session('firebase.uid');
                                        $isTargetSuper = ($user['role'] ?? 'warga') === 'super_admin';
                                        $canManage = in_array(session('firebase.role'), ['rw', 'super_admin'], true);
                                        $canEdit = $canManage && ! $isSelf && (! $isTargetSuper || session('firebase.role') === 'super_admin');
                                        $canDelete = session('firebase.role') === 'super_admin' && ! $isSelf && ! $isTargetSuper;
                                    @endphp
                                    @if ($canEdit || $canDelete)
                                        <div class="flex justify-end gap-2">
                                            @if ($canEdit)
                                                <a href="{{ route('admin.users.edit', $user['uid']) }}"
                                                   class="rounded-xl border border-brand-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">Ubah</a>
                                            @endif
                                            @if ($canDelete)
                                                <form method="POST" action="{{ route('admin.users.destroy', $user['uid']) }}"
                                                      onsubmit="return confirm('Hapus pengguna &quot;{{ $user['nama'] ?? 'tanpa nama' }}&quot;? Tindakan ini tidak dapat dibatalkan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <nav class="mt-8 flex items-center justify-between text-sm">
            @if ($page > 1)
                <a href="{{ route('admin.users.index', array_filter(['role' => $roleAktif, 'q' => $search ?: null, 'page' => $page - 1])) }}" class="btn-ghost">&larr; Sebelumnya</a>
            @else
                <span></span>
            @endif

            <span class="text-slate-500">Halaman {{ $page }}</span>

            @if ($hasMore)
                <a href="{{ route('admin.users.index', array_filter(['role' => $roleAktif, 'q' => $search ?: null, 'page' => $page + 1])) }}" class="btn-ghost">Berikutnya &rarr;</a>
            @else
                <span></span>
            @endif
        </nav>
    @endif
@endsection
