<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') &middot; Laporku</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-800 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-2">
        {{-- Panel brand --}}
        <aside class="relative hidden overflow-hidden bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-brand-300/25 blur-3xl"></div>

            <a href="{{ route('home') }}" class="relative flex items-center gap-2.5">
                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/15 text-lg font-bold backdrop-blur">L</span>
                <span class="text-xl font-semibold tracking-tight">Laporku</span>
            </a>

            <div class="relative max-w-md">
                <h2 class="text-3xl font-bold leading-tight">Lingkungan perumahan yang aman &amp; rapi, dimulai dari satu laporan.</h2>
                <p class="mt-4 text-brand-50/90">
                    Laporkan gangguan keamanan, kerusakan fasilitas, hingga masalah kebersihan.
                    Pengurus menindaklanjuti, dan Anda dapat memantau perkembangannya.
                </p>

                <ul class="mt-8 space-y-3 text-sm">
                    @foreach ([
                        'Peta laporan lingkungan real-time',
                        'Lampirkan foto atau video sebagai bukti',
                        'Notifikasi email setiap perubahan status',
                    ] as $feature)
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs text-brand-100/80">&copy; {{ date('Y') }} Laporku. Sistem Pelaporan Lingkungan Perumahan.</p>
        </aside>

        {{-- Form --}}
        <main class="flex min-h-screen items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2.5 lg:hidden">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 text-lg font-bold text-white shadow-sm">L</span>
                    <span class="text-xl font-semibold tracking-tight text-slate-900">Laporku</span>
                </a>

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
