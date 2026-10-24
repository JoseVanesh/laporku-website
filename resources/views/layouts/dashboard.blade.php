@php
    $role = session('firebase.role');
    $roleLabel = ['warga' => 'Warga', 'rt' => 'RT/RW', 'admin' => 'Admin'][$role] ?? $role;
    $isStaff = in_array($role, ['admin', 'rt'], true);
    $initial = mb_strtoupper(mb_substr((string) session('firebase.name', 'L'), 0, 1));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') &middot; Laporku</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-mist-50 text-slate-800 antialiased">
    <div class="min-h-screen lg:flex">
        {{-- Sidebar (desktop) --}}
        <aside class="hidden w-64 shrink-0 flex-col border-r border-brand-100 bg-white lg:flex lg:sticky lg:top-0 lg:h-screen">
            <a href="{{ route('dashboard') }}" class="flex h-16 items-center gap-2.5 border-b border-brand-50 px-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-bold text-white shadow-sm">L</span>
                <span class="text-lg font-semibold tracking-tight text-slate-900">Laporku</span>
            </a>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Menu</p>

                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                    Dashboard
                </a>

                @if (! $isStaff)
                    <a href="{{ route('laporan.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('laporan.index', 'laporan.show') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                        Laporan Saya
                    </a>
                    <a href="{{ route('laporan.create') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('laporan.create') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Buat Laporan
                    </a>
                @endif

                @if ($isStaff)
                    <p class="px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Pengurus</p>
                    <a href="{{ route('admin.laporan.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.laporan.*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
                        Kelola Laporan
                    </a>
                @endif

                @if ($role === 'admin')
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                        Pengguna
                    </a>
                    <a href="{{ route('admin.kategori.index') }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.kategori.*') ? 'bg-brand-600 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                        Jenis Laporan
                    </a>
                @endif
            </nav>

            <div class="border-t border-brand-50 p-3">
                <div class="flex items-center gap-3 rounded-xl bg-mist-50 px-3 py-2.5 ring-1 ring-inset ring-brand-100">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">{{ $initial }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800">{{ session('firebase.name') }}</p>
                        <p class="truncate text-xs text-slate-500">{{ $roleLabel }}</p>
                    </div>
                </div>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl border border-brand-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-40 border-b border-brand-100 bg-white/85 backdrop-blur">
                <div class="flex h-16 items-center justify-between gap-4 px-4 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 lg:hidden">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-bold text-white shadow-sm">L</span>
                        </a>
                        <div class="min-w-0">
                            <h1 class="truncate text-base font-semibold text-slate-900 sm:text-lg">@yield('title', 'Dashboard')</h1>
                            <p class="hidden truncate text-xs text-slate-500 sm:block">@yield('subtitle', 'Sistem Pelaporan Lingkungan')</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="hidden items-center gap-2 rounded-full bg-mist-100 px-3 py-1.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-brand-100 md:inline-flex">
                            <span class="h-2 w-2 rounded-full bg-brand-500"></span>
                            {{ $roleLabel }}
                        </span>
                        <form id="logout-form-mobile" method="POST" action="{{ route('logout') }}" class="lg:hidden">
                            @csrf
                            <button type="submit" class="rounded-xl border border-brand-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">Keluar</button>
                        </form>
                    </div>
                </div>

                {{-- Mobile nav --}}
                <nav class="flex gap-2 overflow-x-auto px-4 pb-3 lg:hidden">
                    @php
                        $mobileLinks = [['dashboard', 'Dashboard']];
                        if (! $isStaff) {
                            $mobileLinks[] = ['laporan.index', 'Laporan Saya'];
                            $mobileLinks[] = ['laporan.create', 'Buat'];
                        } else {
                            $mobileLinks[] = ['admin.laporan.index', 'Kelola'];
                        }
                        if ($role === 'admin') {
                            $mobileLinks[] = ['admin.users.index', 'Pengguna'];
                            $mobileLinks[] = ['admin.kategori.index', 'Jenis'];
                        }
                    @endphp
                    @foreach ($mobileLinks as [$name, $label])
                        <a href="{{ route($name) }}"
                           class="whitespace-nowrap rounded-full px-3.5 py-1.5 text-sm font-medium ring-1 ring-inset transition {{ request()->routeIs($name) ? 'bg-brand-600 text-white ring-brand-600' : 'bg-white text-slate-600 ring-brand-100' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </header>

            <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
