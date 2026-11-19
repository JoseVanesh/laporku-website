<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') &middot; Laporku</title>
    @include('partials.firebase-config')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-mist-50 font-sans text-slate-900 antialiased">
    @php
        $navClass = fn (string $pattern) => 'rounded-md px-3 py-2 text-sm font-medium transition '
            . (request()->routeIs($pattern)
                ? 'bg-brand-50 text-brand-800 ring-1 ring-inset ring-brand-200'
                : 'text-slate-600 hover:bg-white hover:text-slate-900');
    @endphp

    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                <span class="brand-mark">L</span>
                <span class="font-display text-lg font-semibold tracking-tight text-slate-900" style="font-family:var(--font-display)">Laporku</span>
            </a>

            <div class="flex items-center gap-1 sm:gap-2">
                @if (session('firebase.uid'))
                    <a href="{{ route('dashboard') }}" class="{{ $navClass('dashboard') }}">Dashboard</a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ $navClass('login') }}">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary py-2">Daftar</a>
                @endif
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-7 text-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-brand-700 text-xs font-bold text-white" style="font-family:var(--font-display)">L</span>
                <span class="font-medium text-slate-800">Laporku</span>
                <span class="text-slate-300">&middot;</span>
                <span class="text-slate-500">Pelaporan lingkungan perumahan</span>
            </div>
            <p class="text-slate-500">&copy; {{ date('Y') }} Dilaporkan warga, ditindaklanjuti pengurus.</p>
        </div>
    </footer>
</body>
</html>
