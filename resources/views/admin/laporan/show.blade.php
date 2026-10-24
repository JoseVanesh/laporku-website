@extends('layouts.dashboard')

@section('title', 'Tangani Laporan')
@section('subtitle', 'Tinjau dan ubah status laporan')

@section('content')
    <div class="mx-auto max-w-5xl">
        <a href="{{ route('admin.laporan.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">
            &larr; Kembali ke daftar laporan
        </a>

        <div class="mt-4 grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="card p-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-mist-100 px-2 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-100">{{ $laporan['kategori'] }}</span>
                        @include('partials.status-badge', ['status' => $laporan['status'], 'labels' => $statusLabels])
                    </div>

                    <h1 class="mt-3 text-2xl font-semibold text-slate-900">{{ $laporan['judul'] }}</h1>

                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500">
                        <span>{{ $laporan['nama_penulis'] ?? '-' }} (Blok {{ $laporan['blok'] ?: '-' }})</span>
                        <span>{{ $laporan['email_penulis'] ?? '' }}</span>
                        <span>Dikirim {{ \Illuminate\Support\Carbon::parse($laporan['created_at'])->locale('id')->translatedFormat('d MMMM yyyy, HH:mm') }}</span>
                        @if (! empty($laporan['lokasi']))
                            <span>📍 {{ $laporan['lokasi'] }}</span>
                        @endif
                    </div>

                    <p class="mt-4 whitespace-pre-line leading-relaxed text-slate-700">{{ $laporan['deskripsi'] }}</p>

                    @if (! empty($laporan['media']))
                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach ($laporan['media'] as $item)
                                @if (($item['tipe'] ?? 'image') === 'video')
                                    <video controls preload="metadata" class="w-full rounded-xl border border-brand-100 bg-black" src="{{ $item['url'] }}"></video>
                                @else
                                    <a href="{{ $item['url'] }}" target="_blank" rel="noopener">
                                        <img src="{{ $item['url'] }}" alt="Lampiran laporan" class="w-full rounded-xl border border-brand-100 object-cover" loading="lazy">
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card mt-6 p-6">
                    <h2 class="font-semibold text-slate-900">Komentar &amp; Catatan</h2>

                    <div class="mt-4 space-y-4">
                        @forelse ($komentar as $k)
                            <div class="rounded-xl border {{ ! empty($k['sistem']) ? 'border-brand-100 bg-brand-50/60' : 'border-brand-50 bg-mist-50' }} p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                                    <span class="font-semibold text-slate-700">
                                        {{ $k['nama'] ?? '-' }}
                                        @if (! empty($k['sistem']))
                                            <span class="ml-1 rounded bg-brand-100 px-1.5 py-0.5 font-medium text-brand-700">Update Status</span>
                                        @else
                                            <span class="ml-1 rounded bg-slate-200 px-1.5 py-0.5 font-medium text-slate-600">{{ ['warga' => 'Warga', 'rt' => 'RT/RW', 'admin' => 'Admin'][$k['peran'] ?? 'warga'] ?? '-' }}</span>
                                        @endif
                                    </span>
                                    <span class="text-slate-400">@include('partials.tanggal', ['waktu' => $k['created_at']])</span>
                                </div>
                                <p class="mt-2 text-sm leading-relaxed text-slate-700">{{ $k['isi'] }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada komentar.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('admin.laporan.comment', $laporan['id']) }}" class="mt-5">
                        @csrf
                        <label for="isi" class="block text-sm font-medium text-slate-700">Balas ke pemilik laporan</label>
                        <textarea id="isi" name="isi" rows="3" required minlength="2" maxlength="1000"
                                  placeholder="Jelaskan tindakan yang dilakukan atau minta informasi tambahan..." class="field mt-1.5">{{ old('isi') }}</textarea>
                        <button type="submit" class="btn-primary mt-3">Kirim Komentar</button>
                    </form>
                </div>
            </div>

            <aside class="space-y-4">
                @if (! empty($laporan['lat']) && ! empty($laporan['lng']))
                    <div class="card overflow-hidden">
                        <div class="border-b border-brand-50 px-5 py-3">
                            <h3 class="text-sm font-semibold text-slate-900">Titik Lokasi</h3>
                        </div>
                        <div id="point-map" class="h-56 w-full"
                             data-lat="{{ $laporan['lat'] }}" data-lng="{{ $laporan['lng'] }}"
                             data-zoom="{{ config('laporku.map.zoom') }}"></div>
                    </div>
                @endif

                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Ubah Status</h3>
                    <p class="mt-1 text-xs text-slate-500">Pemilik laporan akan menerima notifikasi email.</p>

                    <form method="POST" action="{{ route('admin.laporan.status', $laporan['id']) }}" class="mt-4 space-y-3">
                        @csrf
                        @foreach ($statusLabels as $kode => $label)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border {{ old('status', $laporan['status']) === $kode ? 'border-brand-300 bg-brand-50' : 'border-slate-200' }} px-3.5 py-2.5 text-sm transition hover:bg-brand-50/60">
                                <input type="radio" name="status" value="{{ $kode }}" @checked(old('status', $laporan['status']) === $kode)
                                       class="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500">
                                <span class="font-medium text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                        <button type="submit" class="btn-primary w-full">Simpan Status</button>
                    </form>
                </div>

                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Info Laporan</h3>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">ID</dt>
                            <dd class="truncate font-medium text-slate-700" title="{{ $laporan['id'] }}">{{ $laporan['id'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Diperbarui</dt>
                            <dd class="font-medium text-slate-700">@include('partials.tanggal', ['waktu' => $laporan['updated_at'] ?? $laporan['created_at']])</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-slate-500">Lampiran</dt>
                            <dd class="font-medium text-slate-700">{{ (int) ($laporan['jmedia'] ?? count($laporan['media'] ?? [])) }} berkas</dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </div>
@endsection
