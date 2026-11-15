@extends('layouts.dashboard')

@section('title', $item['judul'])
@section('subtitle', 'Pengumuman')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('pengumuman.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500">
            &larr; Kembali ke pengumuman
        </a>

        <div class="card mt-4 p-6">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $item['judul'] }}</h1>

            <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500">
                <span>Oleh {{ $item['penulis_nama'] ?? 'Pengurus' }}</span>
                <span>Diterbitkan {{ \Illuminate\Support\Carbon::parse($item['created_at'])->locale('id')->translatedFormat('d MMMM yyyy, HH:mm') }}</span>
                @if (! empty($item['updated_at']))
                    <span>Diperbarui {{ \Illuminate\Support\Carbon::parse($item['updated_at'])->locale('id')->translatedFormat('d MMMM yyyy, HH:mm') }}</span>
                @endif
            </div>

            <p class="mt-4 whitespace-pre-line leading-relaxed text-slate-700">{{ $item['isi'] }}</p>
        </div>
    </div>
@endsection
