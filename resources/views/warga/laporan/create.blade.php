@extends('layouts.dashboard')

@section('title', 'Buat Laporan')
@section('subtitle', 'Lengkapi data kejadian dan tandai lokasinya')

@section('content')
    <div class="mx-auto max-w-5xl">
        <a href="{{ route('laporan.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">
            &larr; Kembali ke laporan saya
        </a>

        <div class="mt-4 grid gap-6 lg:grid-cols-3">
            <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data"
                  class="card space-y-5 p-6 lg:col-span-2">
                @csrf

                <div>
                    <label for="kategori" class="block text-sm font-medium text-slate-700">Jenis Pelaporan <span class="text-rose-500">*</span></label>
                    <select id="kategori" name="kategori" required class="field mt-1.5">
                        <option value="">-- Pilih jenis pelaporan --</option>
                        @foreach ($kategori as $item)
                            <option value="{{ $item['nama'] }}" @selected(old('kategori') === $item['nama'])>{{ $item['nama'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="judul" class="block text-sm font-medium text-slate-700">Judul Kejadian <span class="text-rose-500">*</span></label>
                    <input type="text" id="judul" name="judul" required minlength="5" maxlength="150"
                           value="{{ old('judul') }}" placeholder="mis. Lampu jalan di depan blok B mati" class="field mt-1.5">
                </div>

                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-slate-700">Uraian Kejadian <span class="text-rose-500">*</span></label>
                    <textarea id="deskripsi" name="deskripsi" required rows="5" minlength="10" maxlength="3000"
                              placeholder="Jelaskan apa yang terjadi, kapan, dan di mana secara rinci." class="field mt-1.5">{{ old('deskripsi') }}</textarea>
                </div>

                <div>
                    <label for="lokasi" class="block text-sm font-medium text-slate-700">Keterangan Lokasi</label>
                    <input type="text" id="lokasi" name="lokasi" maxlength="200" value="{{ old('lokasi') }}"
                           placeholder="mis. Depan rumah nomor 12, samping pos keamanan" class="field mt-1.5">
                </div>

                <input type="hidden" id="lat" name="lat" value="{{ old('lat') }}">
                <input type="hidden" id="lng" name="lng" value="{{ old('lng') }}">

                <div>
                    <label for="media" class="block text-sm font-medium text-slate-700">Foto / Video Bukti <span class="text-rose-500">*</span></label>
                    <input type="file" id="media" name="media[]" multiple required
                           accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime"
                           class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                    <p class="mt-2 text-xs text-slate-500">Maksimal 6 berkas, masing-masing maksimal 50 MB. Format: JPG, PNG, WEBP, MP4, WEBM, MOV.</p>
                    <ul id="media-list" class="mt-2 hidden space-y-1 text-xs text-slate-600"></ul>
                </div>

                <div class="flex items-center gap-3 border-t border-brand-50 pt-5">
                    <button type="submit" class="btn-primary">Kirim Laporan</button>
                    <a href="{{ route('laporan.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Batal</a>
                </div>
            </form>

            <aside class="lg:col-span-1">
                <div class="card sticky top-24 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm font-semibold text-slate-900">Titik Lokasi (opsional)</h2>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Klik pada peta atau geser pin. Agar muncul di peta dashboard, tandai titik lokasi.</p>

                    <div class="mt-3 flex gap-2">
                        <button type="button" id="use-gps" class="btn-ghost flex-1 text-xs">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            Lokasi Saya
                        </button>
                        <button type="button" id="clear-location" class="btn-ghost text-xs">Hapus</button>
                    </div>

                    <div id="location-picker" class="mt-3 h-72 overflow-hidden rounded-xl border border-brand-100"
                         data-lat-input="lat" data-lng-input="lng"
                         data-gps-button="use-gps" data-clear-button="clear-location"
                         data-center-lat="{{ config('laporku.map.center.lat') }}"
                         data-center-lng="{{ config('laporku.map.center.lng') }}"
                         data-zoom="{{ config('laporku.map.zoom') }}"></div>

                    <p data-location-hint class="mt-2 hidden text-xs font-medium text-brand-600"></p>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('media')?.addEventListener('change', function () {
                const list = document.getElementById('media-list');
                list.innerHTML = '';
                list.classList.toggle('hidden', this.files.length === 0);

                for (const file of this.files) {
                    const item = document.createElement('li');
                    const mb = (file.size / (1024 * 1024)).toFixed(1);
                    item.textContent = `${file.name} (${mb} MB)`;
                    list.appendChild(item);
                }
            });
        </script>
    @endpush
@endsection
