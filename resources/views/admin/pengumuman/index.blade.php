@extends('layouts.dashboard')

@section('title', 'Kelola Pengumuman')
@section('subtitle', 'Buat dan atur pengumuman untuk warga')

@section('content')
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('admin.pengumuman.store') }}" class="card p-5"
              data-announcement-form
              data-cloud-name="{{ config('laporku.cloudinary.cloud_name') }}"
              data-upload-preset="{{ config('laporku.cloudinary.upload_preset') }}">
            @csrf
            <div>
                <label for="judul" class="block text-sm font-medium text-slate-700">Judul</label>
                <input type="text" id="judul" name="judul" required minlength="3" maxlength="150" value="{{ old('judul') }}"
                       placeholder="Judul pengumuman (mis. Kerja Bakti Bulan Ini)" class="field mt-1.5">
            </div>
            <div class="mt-4">
                <label for="isi" class="block text-sm font-medium text-slate-700">Isi</label>
                <textarea id="isi" name="isi" required minlength="5" maxlength="5000" rows="5"
                          placeholder="Tulis isi pengumuman..." class="field mt-1.5">{{ old('isi') }}</textarea>
            </div>
            <div class="mt-4">
                <label for="gambar" class="block text-sm font-medium text-slate-700">Gambar Cover <span class="text-slate-400">(opsional)</span></label>
                <input type="file" id="gambar" name="gambar_picker" accept="image/jpeg,image/png,image/webp"
                       data-cover-input
                       class="mt-1.5 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                <p class="mt-2 text-xs text-slate-500">JPG, PNG, atau WEBP maksimal 5 MB. Gambar tampil di dashboard warga.</p>
                <img data-cover-preview alt="Pratinjau gambar" class="mt-3 hidden max-h-48 w-full rounded-xl border border-brand-100 object-cover">
                <p data-cover-error class="mt-2 hidden text-xs font-medium text-rose-600"></p>
                <p data-cover-progress class="mt-2 hidden text-xs font-medium text-brand-600"></p>
            </div>
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-slate-500">Pengumuman tampil di dashboard dan menu Pengumuman untuk semua warga.</p>
                <button type="submit" class="btn-primary">Terbitkan</button>
            </div>
        </form>

        @if (empty($pengumuman))
            <div class="card mt-6 border-dashed p-8 text-center text-sm text-slate-500">
                Belum ada pengumuman. Terbitkan pengumuman pertama di atas.
            </div>
        @else
            <ul class="card mt-6 divide-y divide-brand-50 overflow-hidden">
                @foreach ($pengumuman as $item)
                    <li class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                        <div class="flex min-w-0 flex-1 items-start gap-3">
                            @if (! empty($item['gambar']['url']))
                                <img src="{{ $item['gambar']['url'] }}" alt="" class="h-16 w-20 shrink-0 rounded-xl border border-brand-100 object-cover" loading="lazy">
                            @endif
                            <div class="min-w-0">
                            <p class="font-medium text-slate-900">{{ $item['judul'] }}</p>
                            <p class="mt-0.5 line-clamp-2 text-sm text-slate-500">{{ $item['isi'] }}</p>
                            <p class="mt-1 text-xs text-slate-400">
                                Oleh {{ $item['penulis_nama'] ?? 'Pengurus' }} &middot; Diterbitkan
                                @include('partials.tanggal', ['waktu' => $item['created_at'], 'format' => 'd M Y'])
                            </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <a href="{{ route('admin.pengumuman.edit', $item['id']) }}"
                               class="rounded-xl border border-brand-200 px-3.5 py-2 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">
                                Ubah
                            </a>
                            <form method="POST" action="{{ route('admin.pengumuman.destroy', $item['id']) }}"
                                  onsubmit="return confirm('Hapus pengumuman &quot;{{ $item['judul'] }}&quot;? Pengumuman yang dihapus tidak bisa dikembalikan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="rounded-xl border border-rose-200 px-3.5 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <nav class="mt-6 flex items-center justify-between text-sm">
                @if ($page > 1)
                    <a href="{{ route('admin.pengumuman.index', ['page' => $page - 1]) }}" class="btn-ghost">
                        &larr; Sebelumnya
                    </a>
                @else
                    <span></span>
                @endif

                <span class="text-slate-500">Halaman {{ $page }}</span>

                @if ($hasMore)
                    <a href="{{ route('admin.pengumuman.index', ['page' => $page + 1]) }}" class="btn-ghost">
                        Berikutnya &rarr;
                    </a>
                @else
                    <span></span>
                @endif
            </nav>
        @endif
    </div>
@endsection
