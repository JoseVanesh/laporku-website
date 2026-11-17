@extends('layouts.dashboard')

@section('title', 'Analisis Laporan')
@section('subtitle', 'Frekuensi dan sebaran laporan lingkungan')

@section('content')
    @php
        $byStatus = $analytics['by_status'] ?? [];
        $byBlok = $analytics['by_blok'] ?? [];
        $byKategori = $analytics['by_kategori'] ?? [];
        $total = (int) ($analytics['total'] ?? 0);
        $cartu = [
            ['Total laporan', $total, 'text-slate-900'],
            ['Baru', (int) ($byStatus['baru'] ?? 0), 'text-amber-600'],
            ['Diproses', (int) ($byStatus['diproses'] ?? 0), 'text-sky-600'],
            ['Selesai', (int) ($byStatus['selesai'] ?? 0), 'text-brand-700'],
            ['Tingkat selesai', ($analytics['completion_rate'] ?? 0).'%', 'text-brand-700'],
        ];
        $blokRows = array_map(static fn (array $row): array => [
            'label' => 'Blok '.$row['blok'],
            'total' => (int) $row['total'],
        ], $byBlok);
        $kategoriRows = array_map(static fn (array $row): array => [
            'label' => $row['nama'],
            'total' => (int) $row['total'],
        ], $byKategori);
        $isRt = $role === 'rt';
        $isRw = $role === 'rw';
        $peakHour = $analytics['peak_hour'] ?? null;
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500">
                @if ($isRt)
                    Analisis khusus <span class="font-semibold text-slate-700">{{ $blokLabel }}</span>.
                @elseif ($isRw)
                    Analisis <span class="font-semibold text-slate-700">{{ $blokLabel }}</span> yang Anda urus.
                @elseif ($role === 'super_admin')
                    Analisis global seluruh blok lingkungan.
                @else
                    Analisis frekuensi laporan dan perbandingan tiap blok.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach ([7 => '7 hari', 30 => '30 hari', 90 => '90 hari'] as $value => $label)
                <a href="{{ request()->fullUrlWithQuery(['days' => $value]) }}"
                   class="chip-toggle {{ $days === $value ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @if (($isRt && trim((string) session('firebase.blok', '')) === '') || ($isRw && empty($blocks)))
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Akun {{ $isRw ? 'RW' : 'RT' }} Anda belum memiliki blok. Minta Super Admin mengatur blok Anda di menu Pengguna.
        </div>
    @endif

    <form method="GET" action="{{ route('analisis.index') }}" class="card mb-6 p-4">
        <input type="hidden" name="days" value="{{ $days }}">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="status" class="block text-xs font-medium text-slate-500">Status</label>
                <select id="status" name="status" class="field mt-1 w-auto py-2">
                    <option value="">Semua status</option>
                    @foreach ($statusLabels as $kode => $label)
                        <option value="{{ $kode }}" @selected($filters['status'] === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="kategori" class="block text-xs font-medium text-slate-500">Jenis laporan</label>
                <select id="kategori" name="kategori" class="field mt-1 w-auto py-2">
                    <option value="">Semua jenis</option>
                    @foreach ($kategoriList as $nama)
                        <option value="{{ $nama }}" @selected($filters['kategori'] === $nama)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            @if (! $isRt)
                <div>
                    <label for="blok" class="block text-xs font-medium text-slate-500">Blok</label>
                    <select id="blok" name="blok" class="field mt-1 w-auto py-2">
                        <option value="">Semua blok</option>
                        @foreach ($blocks as $item)
                            <option value="{{ $item['blok'] }}" @selected(($filters['blok'] ?? '') === $item['blok'])>Blok {{ $item['blok'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit" class="btn-ghost py-2">Terapkan</button>

            @if ($filters['status'] || $filters['kategori'] || ($filters['blok'] ?? null))
                <a href="{{ route('analisis.index', ['days' => $days]) }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">Reset</a>
            @endif
        </div>
    </form>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ($cartu as [$label, $value, $valueClass])
            <div class="card p-5">
                <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold tracking-tight {{ $valueClass }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card overflow-hidden xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-brand-50 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">Tren Frekuensi Laporan</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ $days }} hari terakhir &middot; rata-rata {{ $analytics['avg_per_day'] ?? 0 }} laporan/hari &middot; {{ $analytics['active_days'] ?? 0 }} hari aktif</p>
                </div>
            </div>
            <div class="px-4 py-5 sm:px-5">
                @include('partials.analytics.trend', ['daily' => $analytics['daily'] ?? []])
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-brand-50 px-5 py-4">
                <h3 class="font-semibold text-slate-900">Distribusi Status</h3>
                <p class="mt-1 text-xs text-slate-500">Perbandingan status laporan pada rentang ini.</p>
            </div>
            <div class="px-5 py-5">
                @include('partials.analytics.status', [
                    'byStatus' => $byStatus,
                    'total' => $total,
                    'statusLabels' => $statusLabels,
                ])
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-brand-50 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">{{ $isRt ? 'Frekuensi Blok Anda' : 'Frekuensi per Blok' }}</h3>
                    <p class="mt-1 text-xs text-slate-500">Jumlah kejadian yang dilaporkan warga.</p>
                </div>
            </div>
            <div class="px-5 py-5">
                @include('partials.analytics.bars', [
                    'items' => $blokRows,
                    'empty' => 'Belum ada laporan yang memiliki data blok.',
                ])
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-brand-50 px-5 py-4">
                <h3 class="font-semibold text-slate-900">Jenis Laporan Terbanyak</h3>
                <p class="mt-1 text-xs text-slate-500">Kategori kejadian yang paling sering dilaporkan.</p>
            </div>
            <div class="px-5 py-5">
                @include('partials.analytics.bars', [
                    'items' => $kategoriRows,
                    'color' => 'bg-sky-500',
                    'empty' => 'Belum ada data jenis laporan.',
                ])
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <div class="card overflow-hidden">
            <div class="border-b border-brand-50 px-5 py-4">
                <h3 class="font-semibold text-slate-900">Hari Paling Aktif</h3>
                <p class="mt-1 text-xs text-slate-500">Distribusi laporan berdasarkan hari dalam seminggu.</p>
            </div>
            <div class="px-5 py-5">
                @include('partials.analytics.day-bars', ['byHari' => $analytics['by_hari'] ?? []])
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="border-b border-brand-50 px-5 py-4">
                <h3 class="font-semibold text-slate-900">Waktu Rawan</h3>
                <p class="mt-1 text-xs text-slate-500">Jam tersibuk berdasarkan waktu laporan dibuat.</p>
            </div>
            <div class="flex flex-wrap items-center gap-6 px-5 py-5">
                <div class="rounded-2xl bg-brand-50 px-5 py-4 text-center ring-1 ring-inset ring-brand-100">
                    <p class="text-xs font-medium text-brand-700">Jam tersibuk</p>
                    <p class="mt-1 text-3xl font-bold text-brand-800">
                        {{ $peakHour !== null ? sprintf('%02d:00', $peakHour) : '-' }}
                    </p>
                    <p class="mt-1 text-xs text-brand-600">
                        {{ $peakHour !== null ? ((int) ($analytics['by_jam'][$peakHour] ?? 0)).' laporan' : 'Belum ada data' }}
                    </p>
                </div>
                <div class="min-w-0 flex-1 text-sm text-slate-600">
                    <p class="font-medium text-slate-800">Gunakan informasi ini untuk menentukan jadwal patroli atau penanganan.</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">Data dihitung dari laporan terbaru pada rentang {{ $days }} hari. Laporan tanpa tanggal valid tidak dihitung.</p>
                </div>
            </div>
        </div>
    </div>

    @if (! $isRt && ! empty($byBlok))
        <div class="card mt-6 overflow-hidden">
            <div class="border-b border-brand-50 px-5 py-4">
                <h3 class="font-semibold text-slate-900">Perbandingan Blok</h3>
                <p class="mt-1 text-xs text-slate-500">Rincian status tiap blok pada rentang {{ $days }} hari.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-mist-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Blok</th>
                            <th class="px-4 py-3">Total</th>
                            @foreach ($statusLabels as $label)
                                <th class="px-4 py-3">{{ $label }}</th>
                            @endforeach
                            <th class="px-4 py-3">Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @foreach ($byBlok as $row)
                            @php($rate = ((int) $row['total']) > 0 ? round(((int) ($row['selesai'] ?? 0) / (int) $row['total']) * 100, 1) : 0)
                            <tr class="transition hover:bg-brand-50/40">
                                <td class="px-4 py-3 font-medium text-slate-900">Blok {{ $row['blok'] }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $row['total'] }}</td>
                                @foreach (array_keys($statusLabels) as $kode)
                                    <td class="px-4 py-3 text-slate-600">{{ (int) ($row[$kode] ?? 0) }}</td>
                                @endforeach
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">{{ $rate }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
