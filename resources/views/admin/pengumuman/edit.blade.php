@extends('layouts.dashboard')

@section('title', 'Ubah Pengumuman')
@section('subtitle', $item['judul'])

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('admin.pengumuman.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">
            &larr; Kembali ke pengumuman
        </a>

        <form method="POST" action="{{ route('admin.pengumuman.update', $item['id']) }}" class="card mt-4 p-5"
              data-announcement-form
              data-cloud-name="{{ config('laporku.cloudinary.cloud_name') }}"
              data-upload-preset="{{ config('laporku.cloudinary.upload_preset') }}">
            @csrf
            @method('PUT')

            <div>
                <label for="judul" class="block text-sm font-medium text-slate-700">Judul</label>
                <input type="text" id="judul" name="judul" required minlength="3" maxlength="150"
                       value="{{ old('judul', $item['judul']) }}" class="field mt-1.5">
            </div>

            <div class="mt-4">
                <label for="isi" class="block text-sm font-medium text-slate-700">Isi</label>
                <textarea id="isi" name="isi" required minlength="5" maxlength="5000" rows="8" class="field mt-1.5">{{ old('isi', $item['isi']) }}</textarea>
            </div>

            <div class="mt-4">
                <label for="gambar" class="block text-sm font-medium text-slate-700">Gambar Cover</label>
                <img data-cover-preview @if (! empty($item['gambar']['url'])) src="{{ $item['gambar']['url'] }}" @endif alt="Gambar pengumuman"
                     class="mt-1.5 max-h-48 w-full rounded-xl border border-brand-100 object-cover {{ empty($item['gambar']['url']) ? 'hidden' : '' }}">
                <input type="file" id="gambar" name="gambar_picker" accept="image/jpeg,image/png,image/webp"
                       data-cover-input
                       class="mt-3 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-brand-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100">
                <p class="mt-2 text-xs text-slate-500">Pilih gambar baru untuk mengganti cover. JPG, PNG, atau WEBP maksimal 5 MB.</p>
                @if (! empty($item['gambar']['url']))
                    <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="hapus_gambar" value="1" data-cover-remove
                               class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        Hapus gambar cover saat menyimpan
                    </label>
                @endif
                <p data-cover-error class="mt-2 hidden text-xs font-medium text-rose-600"></p>
                <p data-cover-progress class="mt-2 hidden text-xs font-medium text-brand-600"></p>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.pengumuman.index') }}" class="btn-ghost">Batal</a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
