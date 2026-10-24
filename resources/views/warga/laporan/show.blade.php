@extends('layouts.dashboard')

@section('title', $laporan['judul'])
@section('subtitle', 'Detail laporan & tindak lanjut')

@section('content')
    <div class="mx-auto max-w-5xl">
        <a href="{{ route('laporan.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">
            &larr; Kembali ke laporan saya
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
                        <span>Dikirim {{ \Illuminate\Support\Carbon::parse($laporan['created_at'])->locale('id')->translatedFormat('d MMMM yyyy, HH:mm') }}</span>
                        @if (! empty($laporan['lokasi']))
                            <span>📍 {{ $laporan['lokasi'] }}</span>
                        @endif
                        @if (! empty($laporan['blok']))
                            <span>Blok {{ $laporan['blok'] }}</span>
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
                    <h2 class="font-semibold text-slate-900">Diskusi &amp; Tindak Lanjut</h2>

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
                            <p class="text-sm text-slate-500">Belum ada komentar. Pengurus akan menanggapi laporan Anda di sini.</p>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('laporan.comment', $laporan['id']) }}" class="mt-5">
                        @csrf
                        <label for="isi" class="block text-sm font-medium text-slate-700">Tulis komentar</label>
                        <textarea id="isi" name="isi" rows="3" required minlength="2" maxlength="1000"
                                  placeholder="Tambahkan informasi tambahan atau tanyakan perkembangan..." class="field mt-1.5">{{ old('isi') }}</textarea>
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
                    <h3 class="text-sm font-semibold text-slate-900">Riwayat Status</h3>
                    <ol class="mt-3 space-y-3 text-sm">
                        @php($timeline = [
                            ['baru', 'Laporan diterima sistem'],
                            ['diproses', 'Sedang ditangani pengurus'],
                            ['selesai', 'Laporan selesai ditangani'],
                            ['ditolak', 'Laporan ditolak dengan catatan'],
                        ])
                        @foreach ($timeline as [$kode, $teks])
                            @php($aktif = $laporan['status'] === $kode)
                            <li class="flex items-start gap-3 {{ $aktif ? 'font-semibold text-slate-900' : 'text-slate-400' }}">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $aktif ? ($kode === 'ditolak' ? 'bg-rose-500' : 'bg-brand-500') : 'bg-slate-300' }}"></span>
                                {{ $teks }}
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="card p-5">
                    <h3 class="text-sm font-semibold text-slate-900">Status Saat Ini</h3>
                    <div class="mt-2">
                        @include('partials.status-badge', ['status' => $laporan['status'], 'labels' => $statusLabels])
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-slate-500">
                        Anda akan menerima notifikasi email setiap kali status laporan ini berubah.
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection
