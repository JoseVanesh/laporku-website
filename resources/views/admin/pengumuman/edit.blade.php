@extends('layouts.dashboard')

@section('title', 'Ubah Pengumuman')
@section('subtitle', $item['judul'])

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('admin.pengumuman.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">
            &larr; Kembali ke pengumuman
        </a>

        <form method="POST" action="{{ route('admin.pengumuman.update', $item['id']) }}" class="card mt-4 p-5">
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

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.pengumuman.index') }}" class="btn-ghost">Batal</a>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
