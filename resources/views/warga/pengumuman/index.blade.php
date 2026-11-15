@extends('layouts.dashboard')

@section('title', 'Pengumuman')
@section('subtitle', 'Informasi & pengumuman untuk warga')

@section('content')
    <div class="mb-6">
        <p class="text-sm text-slate-500">Informasi terbaru dari pengurus perumahan.</p>
    </div>

    @if (empty($pengumuman))
        <div class="card border-dashed p-10 text-center">
            <p class="text-3xl">📢</p>
            <p class="mt-3 font-medium text-slate-700">Belum ada pengumuman.</p>
            <p class="mt-1 text-sm text-slate-500">Pengumuman dari pengurus akan tampil di sini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($pengumuman as $item)
                <a href="{{ route('pengumuman.show', $item['id']) }}" class="card block p-5 transition hover:border-brand-300 hover:shadow-soft">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="truncate font-semibold text-slate-900">{{ $item['judul'] }}</h2>
                            <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $item['isi'] }}</p>
                            <p class="mt-2 text-xs text-slate-500">Oleh {{ $item['penulis_nama'] ?? 'Pengurus' }}</p>
                        </div>
                        <div class="shrink-0 text-right text-xs text-slate-500">
                            <p>@include('partials.tanggal', ['waktu' => $item['created_at']])</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <nav class="mt-8 flex items-center justify-between text-sm">
            @if ($page > 1)
                <a href="{{ route('pengumuman.index', ['page' => $page - 1]) }}" class="btn-ghost">
                    &larr; Sebelumnya
                </a>
            @else
                <span></span>
            @endif

            <span class="text-slate-500">Halaman {{ $page }}</span>

            @if ($hasMore)
                <a href="{{ route('pengumuman.index', ['page' => $page + 1]) }}" class="btn-ghost">
                    Berikutnya &rarr;
                </a>
            @else
                <span></span>
            @endif
        </nav>
    @endif
@endsection
