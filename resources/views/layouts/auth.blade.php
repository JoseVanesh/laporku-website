<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') &middot; Laporku</title>
    @include('partials.firebase-config')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-mist-50 font-sans text-slate-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr]">
        {{-- Panel brand --}}
        <aside class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-950 via-slate-950 to-slate-900"></div>
            <div class="hero-grid pointer-events-none absolute inset-0 opacity-40"></div>
            <div class="pointer-events-none absolute -right-24 top-10 h-72 w-72 rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -left-16 bottom-0 h-72 w-72 rounded-full bg-signal-500/10 blur-3xl"></div>

            <a href="{{ route('home') }}" class="relative flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-600 text-lg font-bold ring-1 ring-inset ring-white/10" style="font-family:var(--font-display)">L</span>
                <span class="text-xl font-semibold tracking-tight" style="font-family:var(--font-display)">Laporku</span>
            </a>

            <div class="relative max-w-md">
                <p class="eyebrow !text-brand-300">Pelaporan Lingkungan</p>
                <h2 class="mt-4 text-3xl font-semibold leading-tight text-white" style="font-family:var(--font-display)">Satu saluran resmi untuk laporan warga dan tindak lanjut pengurus.</h2>
                <p class="mt-4 text-base leading-relaxed text-slate-300">
                    Laporkan keamanan, infrastruktur, hingga kebersihan. Setiap laporan tercatat, terpetakan, dan dapat dipantau statusnya.
                </p>

                <dl class="mt-10 grid grid-cols-3 gap-4 border-t border-white/10 pt-6">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wider text-slate-400">Terpetakan</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">Lokasi presisi</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wider text-slate-400">Tercatat</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">Riwayat status</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wider text-slate-400">Tertangani</dt>
                        <dd class="mt-1 text-lg font-semibold text-white">RT &amp; RW</dd>
                    </div>
                </dl>
            </div>

            <p class="relative text-xs text-slate-400">&copy; {{ date('Y') }} Laporku. Sistem Pelaporan Lingkungan Perumahan.</p>
        </aside>

        {{-- Form --}}
        <main class="flex min-h-screen items-center justify-center px-6 py-12 sm:px-10">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-2.5 lg:hidden">
                    <span class="brand-mark">L</span>
                    <span class="text-xl font-semibold tracking-tight text-slate-900" style="font-family:var(--font-display)">Laporku</span>
                </a>

                <div class="card p-6 sm:p-8">
                    @yield('content')
                </div>

                <p class="mt-6 text-center text-xs text-slate-500 lg:hidden">&copy; {{ date('Y') }} Laporku</p>
            </div>
        </main>
    </div>
</body>
</html>
