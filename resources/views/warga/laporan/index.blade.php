@extends('layouts.dashboard')

@section('title', 'Laporan Saya')
@section('subtitle', 'Pantau status laporan yang Anda kirimkan')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-slate-500">Pantau status laporan yang pernah Anda kirimkan.</p>
        <a href="{{ route('laporan.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Buat Laporan
        </a>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @php($filters = ['' => 'Semua'] + $statusLabels)
        @foreach ($filters as $value => $label)
            <a href="{{ route('laporan.index', array_filter(['status' => $value])) }}"
               class="chip-toggle {{ ($statusAktif ?? null) === ($value ?: null) && ($value !== '' ? true : $statusAktif === null)
                   ? 'is-active'
                   : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if (empty($laporan))
        <div class="card border-dashed p-10 text-center">
            <p class="text-3xl">📋</p>
            <p class="mt-3 font-medium text-slate-700">Belum ada laporan.</p>
            <p class="mt-1 text-sm text-slate-500">Mulai laporkan masalah di lingkungan perumahan Anda.</p>
            <a href="{{ route('laporan.create') }}" class="btn-primary mt-5">Buat Laporan Pertama</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($laporan as $item)
                <a href="{{ route('laporan.show', $item['id']) }}" class="card block p-5 transition hover:border-brand-300 hover:shadow-soft">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-mist-100 px-2 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-100">{{ $item['kategori'] }}</span>
                                @include('partials.status-badge', ['status' => $item['status'], 'labels' => $statusLabels])
                            </div>
                            <h2 class="mt-2 truncate font-semibold text-slate-900">{{ $item['judul'] }}</h2>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $item['deskripsi'] }}</p>
                        </div>
                        <div class="shrink-0 text-right text-xs text-slate-500">
                            <p>@include('partials.tanggal', ['waktu' => $item['created_at']])</p>
                            <p class="mt-1 inline-flex items-center gap-1">{{ (int) ($item['jmedia'] ?? count($item['media'] ?? [])) }} lampiran
                                @if (! empty($item['lat']) && ! empty($item['lng'])) <span title="Ada titik lokasi">📍</span> @endif
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <nav class="mt-8 flex items-center justify-between text-sm">
            @if ($page > 1)
                <a href="{{ route('laporan.index', array_filter(['status' => $statusAktif, 'page' => $page - 1])) }}" class="btn-ghost">
                    &larr; Sebelumnya
                </a>
            @else
                <span></span>
            @endif

            <span class="text-slate-500">Halaman {{ $page }}</span>

            @if ($hasMore)
                <a href="{{ route('laporan.index', array_filter(['status' => $statusAktif, 'page' => $page + 1])) }}" class="btn-ghost">
                    Berikutnya &rarr;
                </a>
            @else
                <span></span>
            @endif
        </nav>
    @endif
@endsection
