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

        @php
            $currentRole = old('role', $user['role'] ?? 'warga');
            $selectedBlocks = (array) old('bloks', $user['bloks'] ?? []);
            if ($selectedBlocks === [] && ! empty($user['blok'])) {
                $selectedBlocks = [$user['blok']];
            }
        @endphp

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
                        <option value="{{ $kode }}" @selected($currentRole === $kode)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">Warga membuat laporan. RT menangani laporan di bloknya. RW mengelola beberapa blok sekaligus. Super Admin dapat mengatur semua peran dan blok.</p>
            </div>

            <div id="blok-single" class="grid grid-cols-2 gap-4 {{ $currentRole === 'rw' ? 'hidden' : '' }}">
                <div>
                    <label for="blok" class="block text-sm font-medium text-slate-700">Blok</label>
                    <input type="text" id="blok" name="blok" maxlength="20" value="{{ old('blok', $user['blok'] ?? '') }}" class="field mt-1.5">
                </div>
                <div>
                    <label for="rumah" class="block text-sm font-medium text-slate-700">No. Rumah</label>
                    <input type="text" id="rumah" name="rumah" maxlength="20" value="{{ old('rumah', $user['rumah'] ?? '') }}" class="field mt-1.5">
                </div>
            </div>

            <div id="blok-multi" class="{{ $currentRole === 'rw' ? '' : 'hidden' }}">
                <span class="block text-sm font-medium text-slate-700">Blok yang Diurus</span>
                <p class="mt-1 text-xs text-slate-500">Pilih blok yang menjadi wilayah kerja RW ini. Laporan dan analisisnya dibatasi pada blok tersebut.</p>
                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-5">
                    @foreach ($blocks as $block)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-brand-100 px-3 py-2 text-sm text-slate-700 transition hover:bg-brand-50">
                            <input type="checkbox" name="bloks[]" value="{{ $block }}"
                                   @checked(in_array($block, $selectedBlocks, true))
                                   class="rounded border-brand-300 text-brand-600 focus:ring-brand-500">
                            Blok {{ $block }}
                        </label>
                    @endforeach
                </div>
                @error('bloks')
                    <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 border-t border-brand-50 pt-5">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Batal</a>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            (function () {
                var role = document.getElementById('role');
                var single = document.getElementById('blok-single');
                var multi = document.getElementById('blok-multi');
                if (!role || !single || !multi) return;

                function sync() {
                    var isRw = role.value === 'rw';
                    single.classList.toggle('hidden', isRw);
                    multi.classList.toggle('hidden', !isRw);
                }

                role.addEventListener('change', sync);
                sync();
            })();
        </script>
    @endpush
@endsection
