<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') &middot; Laporku</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-mist-50 text-slate-800 antialiased">
    @php
        $navClass = fn (string $pattern) => 'rounded-lg px-3 py-2 text-sm font-medium transition '
            . (request()->routeIs($pattern)
                ? 'bg-brand-50 text-brand-700'
                : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700');
    @endphp

    <header class="sticky top-0 z-40 border-b border-brand-100 bg-white/85 backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-bold text-white shadow-sm">L</span>
                <span class="text-lg font-semibold tracking-tight text-slate-900">Laporku</span>
            </a>

            <div class="flex items-center gap-1 sm:gap-2">
                @if (session('firebase.uid'))
                    <a href="{{ route('dashboard') }}" class="{{ $navClass('dashboard') }}">Dashboard</a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-lg border border-brand-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ $navClass('login') }}">Masuk</a>
                    <a href="{{ route('register') }}"
                       class="rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">Daftar</a>
                @endif
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="border-t border-brand-100 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} Laporku &mdash; Sistem Pelaporan Lingkungan Perumahan.</p>
            <p>Dilaporkan oleh warga, ditindaklanjuti oleh pengurus.</p>
        </div>
    </footer>
</body>
</html>
