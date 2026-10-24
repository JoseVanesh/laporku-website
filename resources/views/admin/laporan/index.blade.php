@extends('layouts.dashboard')

@section('title', 'Kelola Laporan')
@section('subtitle', $blokTerbatas ? 'Laporan untuk blok Anda' : 'Semua laporan warga')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <p class="text-sm text-slate-500">
            @if ($blokTerbatas)
                Anda melihat laporan dari blok <span class="font-semibold text-slate-700">{{ session('firebase.blok') ?: '-' }}</span> saja.
            @else
                Semua laporan dari warga perumahan.
            @endif
        </p>

        <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-wrap items-center gap-2">
            <select name="status" onchange="this.form.submit()" class="field w-auto py-2">
                <option value="">Semua Status</option>
                @foreach ($statusLabels as $kode => $label)
                    <option value="{{ $kode }}" @selected($filters['status'] === $kode)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="kategori" onchange="this.form.submit()" class="field w-auto py-2">
                <option value="">Semua Jenis</option>
                @foreach ($kategoriList as $item)
                    <option value="{{ $item['nama'] }}" @selected($filters['kategori'] === $item['nama'])>{{ $item['nama'] }}</option>
                @endforeach
            </select>

            @if (! $blokTerbatas)
                <input type="text" name="blok" value="{{ $filters['blok'] ?? '' }}" placeholder="Filter blok (mis. A)" class="field w-40 py-2">
                <button type="submit" class="btn-ghost py-2">Terapkan</button>
            @endif

            @if ($filters['status'] || $filters['kategori'] || ($filters['blok'] ?? null))
                <a href="{{ route('admin.laporan.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">Reset</a>
            @endif
        </form>
    </div>

    @if ($blokKosong)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Akun RT/RW Anda belum memiliki blok. Minta admin mengatur blok Anda di menu
            <a href="{{ route('admin.users.index') }}" class="font-semibold underline">Pengguna</a>.
        </div>
    @endif

    @if (empty($laporan))
        <div class="card border-dashed p-10 text-center">
            <p class="text-3xl">🗂️</p>
            <p class="mt-3 font-medium text-slate-700">Tidak ada laporan yang cocok dengan filter.</p>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-mist-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Laporan</th>
                            <th class="px-4 py-3">Pelapor</th>
                            <th class="px-4 py-3">Blok</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($laporan as $item)
                            <tr class="transition hover:bg-brand-50/40">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-slate-900">{{ $item['judul'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $item['kategori'] }} &middot; {{ (int) ($item['jmedia'] ?? count($item['media'] ?? [])) }} lampiran
                                        @if (! empty($item['lat']) && ! empty($item['lng'])) &middot; 📍 bertitik @endif
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $item['nama_penulis'] ?? '-' }}
                                    <span class="block text-xs text-slate-400">{{ $item['email_penulis'] ?? '' }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $item['blok'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-slate-500">@include('partials.tanggal', ['waktu' => $item['created_at']])</td>
                                <td class="px-4 py-3">@include('partials.status-badge', ['status' => $item['status'], 'labels' => $statusLabels])</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.laporan.show', $item['id']) }}"
                                       class="rounded-lg bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-100">Tangani</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <nav class="mt-6 flex items-center justify-between text-sm">
            @if ($page > 1)
                <a href="{{ route('admin.laporan.index', array_filter(['status' => $filters['status'], 'kategori' => $filters['kategori'], 'blok' => $filters['blok'], 'page' => $page - 1])) }}" class="btn-ghost">&larr; Sebelumnya</a>
            @else
                <span></span>
            @endif

            <span class="text-slate-500">Halaman {{ $page }}</span>

            @if ($hasMore)
                <a href="{{ route('admin.laporan.index', array_filter(['status' => $filters['status'], 'kategori' => $filters['kategori'], 'blok' => $filters['blok'], 'page' => $page + 1])) }}" class="btn-ghost">Berikutnya &rarr;</a>
            @else
                <span></span>
            @endif
        </nav>
    @endif
@endsection
