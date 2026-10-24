@extends('layouts.dashboard')

@section('title', 'Jenis Pelaporan')
@section('subtitle', 'Kategori yang bisa dipilih warga')

@section('content')
    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('admin.kategori.store') }}" class="card flex flex-wrap items-start gap-3 p-5">
            @csrf
            <div class="min-w-[12rem] flex-1">
                <input type="text" name="nama" required minlength="3" maxlength="50" value="{{ old('nama') }}"
                       placeholder="Nama jenis pelaporan baru (mis. Penerangan Jalan)" class="field">
            </div>
            <button type="submit" class="btn-primary">Tambah</button>
        </form>

        @if (empty($kategori))
            <div class="card mt-6 border-dashed p-8 text-center text-sm text-slate-500">
                Belum ada jenis pelaporan. Tambahkan minimal satu agar warga dapat membuat laporan.
            </div>
        @else
            <ul class="card mt-6 divide-y divide-brand-50 overflow-hidden">
                @foreach ($kategori as $item)
                    <li class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <p class="font-medium text-slate-900">{{ $item['nama'] }}</p>
                            <p class="text-xs text-slate-500">Dibuat @include('partials.tanggal', ['waktu' => $item['created_at'], 'format' => 'd MMM yyyy'])</p>
                        </div>
                        <form method="POST" action="{{ route('admin.kategori.destroy', $item['id']) }}"
                              onsubmit="return confirm('Hapus jenis pelaporan &quot;{{ $item['nama'] }}&quot;? Laporan lama tetap menyimpan nama kategori ini.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="rounded-xl border border-rose-200 px-3.5 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                Hapus
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
