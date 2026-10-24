@extends('layouts.dashboard')

@section('title', 'Pengguna')
@section('subtitle', 'Kelola peran warga, RT/RW, dan admin')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <p class="text-sm text-slate-500">Perubahan peran berlaku setelah pengguna login ulang.</p>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.index') }}"
               class="chip-toggle {{ $roleAktif === null ? 'is-active' : '' }}">Semua</a>
            @foreach ($roles as $kode => $label)
                <a href="{{ route('admin.users.index', ['role' => $kode]) }}"
                   class="chip-toggle {{ $roleAktif === $kode ? 'is-active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if (empty($users))
        <div class="card border-dashed p-10 text-center">
            <p class="font-medium text-slate-700">Belum ada pengguna pada filter ini.</p>
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
                                <td class="px-4 py-3 text-slate-700">{{ $user['blok'] ?: '-' }} {{ ! empty($user['rumah']) ? '/ ' . $user['rumah'] : '' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-mist-100 px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">{{ $roles[$user['role'] ?? 'warga'] ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if (($user['uid'] ?? null) !== session('firebase.uid'))
                                        <a href="{{ route('admin.users.edit', $user['uid']) }}"
                                           class="rounded-xl border border-brand-200 px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">Ubah</a>
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
                <a href="{{ route('admin.users.index', array_filter(['role' => $roleAktif, 'page' => $page - 1])) }}" class="btn-ghost">&larr; Sebelumnya</a>
            @else
                <span></span>
            @endif

            <span class="text-slate-500">Halaman {{ $page }}</span>

            @if ($hasMore)
                <a href="{{ route('admin.users.index', array_filter(['role' => $roleAktif, 'page' => $page + 1])) }}" class="btn-ghost">Berikutnya &rarr;</a>
            @else
                <span></span>
            @endif
        </nav>
    @endif
@endsection
