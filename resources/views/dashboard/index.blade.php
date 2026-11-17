@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('subtitle', 'Pantau laporan lingkungan secara real-time')

@section('content')
    @php
        $greeting = 'Selamat datang, ' . explode(' ', (string) session('firebase.name', 'Warga'))[0];
        $cards = [
            ['Total', $summary['total'], 'text-slate-900', 'bg-mist-100 text-brand-700'],
            ['Baru', $summary['by_status']['baru'] ?? 0, 'text-amber-600', 'bg-amber-50 text-amber-600'],
            ['Diproses', $summary['by_status']['diproses'] ?? 0, 'text-sky-600', 'bg-sky-50 text-sky-600'],
            ['Selesai', $summary['by_status']['selesai'] ?? 0, 'text-brand-700', 'bg-brand-50 text-brand-600'],
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 sm:text-2xl">{{ $greeting }} 👋</h2>
            <p class="mt-1 text-sm text-slate-500">
                @if ($isStaff)
                    Ringkasan laporan warga yang menjadi tanggung jawab Anda.
                @else
                    Pantau laporan lingkungan Anda dan laporan warga lainnya di peta.
                @endif
            </p>
        </div>
        @if (! $isStaff)
            <a href="{{ route('laporan.create') }}" class="btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Buat Laporan
            </a>
        @endif
    </div>

    @if ($blokKosong)
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Akun RT Anda belum memiliki blok. Minta RW mengatur blok Anda di menu
            <a href="{{ route('admin.users.index') }}" class="font-semibold underline">Pengguna</a>.
        </div>
    @endif

    @if ($isSuperAdmin && $superAdminSummary)
        <div class="mb-6 card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-brand-50 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">Pusat Kendali Super Admin</h3>
                    <p class="mt-1 text-xs text-slate-500">Atur peran RT/RW, blok, dan seluruh data lingkungan dari satu tempat.</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="btn-primary py-2">Kelola Pengguna</a>
            </div>
            <div class="grid gap-3 px-5 py-5 sm:grid-cols-3">
                <div class="rounded-xl bg-mist-50 px-4 py-3 ring-1 ring-inset ring-brand-100">
                    <p class="text-xs font-medium text-slate-500">Total Pengguna</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $superAdminSummary['total'] }}</p>
                </div>
                <div class="rounded-xl bg-mist-50 px-4 py-3 ring-1 ring-inset ring-brand-100">
                    <p class="text-xs font-medium text-slate-500">Blok Terdata</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ count($superAdminSummary['blocks']) }}</p>
                    <p class="mt-1 truncate text-xs text-slate-500">{{ implode(', ', $superAdminSummary['blocks']) ?: 'Belum ada blok' }}</p>
                </div>
                <div class="rounded-xl bg-mist-50 px-4 py-3 ring-1 ring-inset ring-brand-100">
                    <p class="text-xs font-medium text-slate-500">Peran</p>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        @foreach ((array) config('laporku.roles') as $kode => $label)
                            <span class="rounded-full bg-white px-2 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">{{ $label }}: {{ $superAdminSummary['by_role'][$kode] ?? 0 }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 border-t border-brand-50 px-5 py-3">
                <a href="{{ route('analisis.index') }}" class="chip-toggle">Analisis Laporan</a>
                <a href="{{ route('admin.laporan.index') }}" class="chip-toggle">Kelola Laporan</a>
                <a href="{{ route('admin.kategori.index') }}" class="chip-toggle">Jenis Laporan</a>
                <a href="{{ route('admin.pengumuman.index') }}" class="chip-toggle">Kelola Pengumuman</a>
            </div>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as [$label, $value, $valueClass, $iconClass])
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $iconClass }}">
                        <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold tracking-tight {{ $valueClass }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="card overflow-hidden xl:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-brand-50 px-5 py-4">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-brand-500"></span>
                    </span>
                    <h3 class="font-semibold text-slate-900">Peta laporan real-time</h3>
                </div>
                <p data-map-status class="text-xs font-medium text-brand-600">Menghubungkan...</p>
            </div>

            <div class="flex flex-wrap gap-2 border-b border-brand-50 px-5 py-3" id="map-filters">
                @php($statusOptions = ['' => 'Semua'] + $statusLabels)
                @foreach ($statusOptions as $value => $label)
                    <button type="button" data-status="{{ $value }}"
                            class="chip-toggle {{ $value === '' ? 'is-active' : '' }}">{{ $label }}</button>
                @endforeach
                <span class="ml-auto self-center text-xs text-slate-500"><span data-map-count>0</span> titik terpetakan</span>
            </div>

            <div id="dashboard-map"
                 class="h-[24rem] w-full"
                 data-points-url="{{ $map['pointsUrl'] }}"
                 data-center-lat="{{ $map['center']['lat'] }}"
                 data-center-lng="{{ $map['center']['lng'] }}"
                 data-zoom="{{ $map['zoom'] }}"></div>

            <div class="border-t border-brand-50 px-5 py-3 text-xs text-slate-500">
                Titik diperbarui otomatis setiap 12 detik.
                @if ($role === 'rt')
                    Menampilkan laporan dari blok <span class="font-semibold text-slate-700">{{ session('firebase.blok') ?: '-' }}</span>.
                @elseif (! $isStaff)
                    Detail laporan warga lain dibatasi; Anda tetap dapat melihat sebarannya di peta.
                @endif
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-brand-50 px-5 py-4">
                <h3 class="font-semibold text-slate-900">{{ $isStaff ? 'Laporan Terbaru' : 'Laporan Saya Terbaru' }}</h3>
                <a href="{{ $isStaff ? route('admin.laporan.index') : route('laporan.index') }}"
                   class="text-xs font-semibold text-brand-600 hover:text-brand-500">Lihat semua</a>
            </div>

            <div class="divide-y divide-brand-50">
                @forelse ($latest as $item)
                    @php($url = $isStaff ? route('admin.laporan.show', $item['id']) : route('laporan.show', $item['id']))
                    <a href="{{ $url }}" class="block px-5 py-3.5 transition hover:bg-brand-50/40">
                        <div class="flex items-center gap-2">
                            <span class="rounded-md bg-mist-100 px-2 py-0.5 text-[11px] font-medium text-brand-700 ring-1 ring-inset ring-brand-100">{{ $item['kategori'] }}</span>
                            @include('partials.status-badge', ['status' => $item['status'], 'labels' => $statusLabels])
                        </div>
                        <p class="mt-1.5 truncate text-sm font-medium text-slate-800">{{ $item['judul'] }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">@include('partials.tanggal', ['waktu' => $item['created_at']])</p>
                    </a>
                @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-2xl">📭</p>
                        <p class="mt-2 text-sm text-slate-500">Belum ada laporan.</p>
                        @if (! $isStaff)
                            <a href="{{ route('laporan.create') }}" class="mt-3 inline-block text-sm font-semibold text-brand-600 hover:text-brand-500">Buat laporan pertama</a>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if ($isStaff)
        <div class="mt-6 grid gap-6 xl:grid-cols-3">
            <div class="card overflow-hidden xl:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-brand-50 px-5 py-4">
                    <div>
                        <h3 class="font-semibold text-slate-900">Tren 7 Hari Terakhir</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $analytics['total'] ?? 0 }} laporan masuk &middot; rata-rata {{ $analytics['avg_per_day'] ?? 0 }} laporan/hari</p>
                    </div>
                    <a href="{{ route('analisis.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-500">Analisis lengkap</a>
                </div>
                <div class="px-4 py-4 sm:px-5">
                    @include('partials.analytics.trend', ['daily' => $analytics['daily'] ?? []])
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-brand-50 px-5 py-4">
                    <div>
                        <h3 class="font-semibold text-slate-900">Laporan per Blok</h3>
                        <p class="mt-1 text-xs text-slate-500">Jumlah laporan kejadian per blok.</p>
                    </div>
                </div>
                <div class="px-5 py-5">
                    @php($blokRows = array_map(static fn (array $row): array => ['label' => 'Blok '.$row['blok'], 'total' => (int) $row['total']], $blokStats['bloks'] ?? []))
                    @include('partials.analytics.bars', [
                        'items' => $blokRows,
                        'empty' => 'Belum ada laporan yang tercatat per blok.',
                    ])
                </div>
            </div>
        </div>
    @endif

    @if ($role === 'rt' && ! empty($perluTindak))
        <div class="mt-6 card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-brand-50 px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">Perlu Ditindak</h3>
                    <p class="mt-1 text-xs text-slate-500">Laporan baru di blok {{ session('firebase.blok') ?: '-' }} yang menunggu respons.</p>
                </div>
                <a href="{{ route('admin.laporan.index', ['status' => 'baru']) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-500">Lihat semua</a>
            </div>
            <div class="divide-y divide-brand-50">
                @foreach ($perluTindak as $item)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-800">{{ $item['judul'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $item['kategori'] }} &middot; @include('partials.tanggal', ['waktu' => $item['created_at']])</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="{{ route('admin.laporan.show', $item['id']) }}" class="btn-ghost px-3 py-1.5 text-xs">Tinjau</a>
                            <form method="POST" action="{{ route('admin.laporan.status', $item['id']) }}">
                                @csrf
                                <input type="hidden" name="status" value="diproses">
                                <button type="submit" class="btn-primary px-3 py-1.5 text-xs">Proses</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if (! $isStaff)
        <div class="mt-6 card overflow-hidden">
            <div class="border-b border-brand-50 px-5 py-4">
                <h3 class="font-semibold text-slate-900">Progres Laporan Saya</h3>
                <p class="mt-1 text-xs text-slate-500">Pantau perkembangan laporan yang Anda kirim.</p>
            </div>
            <div class="px-5 py-5">
                @include('partials.analytics.status', [
                    'byStatus' => $summary['by_status'] ?? [],
                    'total' => $summary['total'] ?? 0,
                    'statusLabels' => $statusLabels,
                ])
            </div>
        </div>
    @endif

    @if (count($pengumuman) > 0)
        <div class="mt-6 card overflow-hidden">
            <div class="flex items-center justify-between border-b border-brand-50 px-5 py-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.3404 15.8398C9.65153 15.7803 8.95431 15.75 8.25 15.75H7.5C5.01472 15.75 3 13.7353 3 11.25C3 8.76472 5.01472 6.75 7.5 6.75H8.25C8.95431 6.75 9.65153 6.71966 10.3404 6.66022M10.3404 15.8398C10.5933 16.8015 10.9237 17.7317 11.3246 18.6234C11.5721 19.1738 11.3842 19.8328 10.8616 20.1345L10.2053 20.5134C9.6539 20.8318 8.9456 20.6306 8.67841 20.0527C8.0518 18.6973 7.56541 17.2639 7.23786 15.771M10.3404 15.8398C9.95517 14.3745 9.75 12.8362 9.75 11.25C9.75 9.66379 9.95518 8.1255 10.3404 6.66022M10.3404 15.8398C13.5 16.1124 16.4845 16.9972 19.1747 18.3749M10.3404 6.66022C13.5 6.3876 16.4845 5.50283 19.1747 4.12509M19.1747 4.12509C19.057 3.74595 18.9302 3.37083 18.7944 3M19.1747 4.12509C19.7097 5.84827 20.0557 7.65462 20.1886 9.51991M19.1747 18.3749C19.057 18.7541 18.9302 19.1292 18.7944 19.5M19.1747 18.3749C19.7097 16.6517 20.0557 14.8454 20.1886 12.9801M20.1886 9.51991C20.6844 9.93264 21 10.5545 21 11.25C21 11.9455 20.6844 12.5674 20.1886 12.9801M20.1886 9.51991C20.2293 10.0913 20.25 10.6682 20.25 11.25C20.25 11.8318 20.2293 12.4087 20.1886 12.9801"/></svg>
                    <h3 class="font-semibold text-slate-900">Pengumuman Terbaru</h3>
                </div>
                <a href="{{ route('pengumuman.index') }}"
                   class="text-xs font-semibold text-brand-600 hover:text-brand-500">Lihat semua</a>
            </div>

            @php($featured = $pengumuman[0] ?? null)
            @if ($featured)
                <a href="{{ route('pengumuman.show', $featured['id']) }}" class="block transition hover:bg-brand-50/40">
                    <div class="grid sm:grid-cols-5">
                        @if (! empty($featured['gambar']['url']))
                            <div class="sm:col-span-2">
                                <img src="{{ $featured['gambar']['url'] }}" alt="" class="h-48 w-full object-cover sm:h-full" loading="lazy">
                            </div>
                        @endif
                        <div class="{{ ! empty($featured['gambar']['url']) ? 'sm:col-span-3' : 'sm:col-span-5' }} p-5">
                            <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Pengumuman terbaru</span>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $featured['judul'] }}</p>
                            <p class="mt-1 line-clamp-3 text-sm text-slate-600">{{ $featured['isi'] }}</p>
                            <p class="mt-3 text-xs text-slate-400">@include('partials.tanggal', ['waktu' => $featured['created_at']])</p>
                        </div>
                    </div>
                </a>
            @endif

            <div class="divide-y divide-brand-50">
                @foreach (array_slice($pengumuman, 1) as $item)
                    <a href="{{ route('pengumuman.show', $item['id']) }}" class="block px-5 py-3.5 transition hover:bg-brand-50/40">
                        <div class="flex items-center gap-3">
                            @if (! empty($item['gambar']['url']))
                                <img src="{{ $item['gambar']['url'] }}" alt="" class="h-11 w-14 shrink-0 rounded-lg border border-brand-100 object-cover" loading="lazy">
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="truncate text-sm font-medium text-slate-800">{{ $item['judul'] }}</p>
                                    <p class="shrink-0 text-xs text-slate-400">@include('partials.tanggal', ['waktu' => $item['created_at']])</p>
                                </div>
                                <p class="mt-0.5 line-clamp-1 text-xs text-slate-500">{{ $item['isi'] }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            (function () {
                var box = document.getElementById('map-filters');
                if (!box) return;

                box.addEventListener('click', function (event) {
                    var button = event.target.closest('button[data-status]');
                    if (!button) return;

                    box.querySelectorAll('button[data-status]').forEach(function (item) {
                        item.classList.toggle('is-active', item === button);
                    });

                    if (window.laporkuDashboardMap) {
                        window.laporkuDashboardMap.setFilter(button.dataset.status);
                    }
                });
            })();
        </script>
    @endpush
@endsection
