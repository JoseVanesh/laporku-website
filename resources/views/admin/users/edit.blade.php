@extends('layouts.dashboard')

@section('title', 'Ubah Pengguna')
@section('subtitle', 'Perbarui data dan peran pengguna')

@section('content')
    <div class="mx-auto max-w-xl">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">
            &larr; Kembali ke daftar pengguna
        </a>

        <p class="mt-3 text-sm text-slate-500">
            {{ $user['email'] ?? '-' }} &middot; perubahan peran berlaku setelah pengguna login ulang.
        </p>

        <form method="POST" action="{{ route('admin.users.update', $user['uid']) }}" class="card mt-4 space-y-5 p-6">
            @csrf
            @method('PUT')

            <div>
                <label for="nama" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required minlength="3" maxlength="100"
                       value="{{ old('nama', $user['nama'] ?? '') }}" class="field mt-1.5">
            </div>

            <div>
                <label for="telepon" class="block text-sm font-medium text-slate-700">No. WhatsApp</label>
                <input type="text" id="telepon" name="telepon" required minlength="9" maxlength="20"
                       value="{{ old('telepon', $user['telepon'] ?? '') }}" class="field mt-1.5">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-slate-700">Peran</label>
                <select id="role" name="role" required class="field mt-1.5">
                    @foreach ($roles as $kode => $label)
                        <option value="{{ $kode }}" @selected(old('role', $user['role'] ?? 'warga') === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Warga hanya membuat laporan. RT/RW menangani laporan di bloknya. Admin mengelola semuanya.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="blok" class="block text-sm font-medium text-slate-700">Blok</label>
                    <input type="text" id="blok" name="blok" maxlength="20" value="{{ old('blok', $user['blok'] ?? '') }}" class="field mt-1.5">
                </div>
                <div>
                    <label for="rumah" class="block text-sm font-medium text-slate-700">No. Rumah</label>
                    <input type="text" id="rumah" name="rumah" maxlength="20" value="{{ old('rumah', $user['rumah'] ?? '') }}" class="field mt-1.5">
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-brand-50 pt-5">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Batal</a>
            </div>
        </form>
    </div>
@endsection
