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
            Akun RT/RW Anda belum memiliki blok. Minta admin mengatur blok Anda di menu
            <a href="{{ route('admin.users.index') }}" class="font-semibold underline">Pengguna</a>.
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
