@extends('layouts.landing')

@section('title', 'Laporkan & Pantau Lingkungan Perumahan')

@section('content')
    @php
        $isLoggedIn = session()->has('firebase.uid');
        $primaryUrl = $isLoggedIn ? route('dashboard') : route('register');
        $primaryLabel = $isLoggedIn ? 'Buka Dashboard' : 'Daftar Sebagai Warga';
    @endphp

    {{-- ===================== NAV ===================== --}}
    <header data-landing-nav class="landing-nav fixed inset-x-0 top-0 z-50 border-b border-transparent">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="brand-mark">L</span>
                <span class="nav-brand text-lg font-semibold tracking-tight" style="font-family:var(--font-display)">Laporku</span>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                <a href="#fitur" class="nav-link rounded-md px-3 py-2 text-sm font-medium transition">Fitur</a>
                <a href="#cara-kerja" class="nav-link rounded-md px-3 py-2 text-sm font-medium transition">Alur</a>
                <a href="#peta" class="nav-link rounded-md px-3 py-2 text-sm font-medium transition">Peta</a>
                <a href="#testimoni" class="nav-link rounded-md px-3 py-2 text-sm font-medium transition">Cerita</a>
            </div>

            <div class="flex items-center gap-2">
                @if ($isLoggedIn)
                    <a href="{{ route('dashboard') }}" class="nav-cta rounded-md px-4 py-2 text-sm font-semibold transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="nav-login hidden rounded-md px-3 py-2 text-sm font-medium transition sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="nav-cta rounded-md px-4 py-2 text-sm font-semibold transition">Daftar</a>
                @endif
            </div>
        </nav>
    </header>

    {{-- ===================== HERO ===================== --}}
    <section class="relative isolate overflow-hidden bg-slate-950 pt-28 pb-20 sm:pt-32 sm:pb-24">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-950 via-slate-950 to-slate-900"></div>
        <div class="hero-grid pointer-events-none absolute inset-0 opacity-50"></div>
        <div class="pointer-events-none absolute -left-24 top-0 h-80 w-80 rounded-full bg-brand-500/20 blur-3xl anim-blob"></div>
        <div class="pointer-events-none absolute -right-16 bottom-0 h-96 w-96 rounded-full bg-signal-500/10 blur-3xl anim-blob" style="animation-delay:-7s"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-4 sm:px-6 lg:grid-cols-[1.05fr_0.95fr]">
            <div>
                <span class="anim-fade-up eyebrow !text-brand-300">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-brand-400 anim-pulse-ring"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-300"></span>
                    </span>
                    Sistem Pelaporan Lingkungan
                </span>

                <h1 class="anim-fade-up anim-delay-1 mt-5 max-w-xl text-4xl font-semibold leading-[1.08] text-white sm:text-5xl lg:text-6xl" style="font-family:var(--font-display)">
                    Laporan warga,<br class="hidden sm:block"> tertangani bersama pengurus.
                </h1>

                <p class="anim-fade-up anim-delay-2 mt-5 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                    Laporku menyalurkan laporan keamanan, infrastruktur, dan kebersihan dari warga ke RT/RW — lengkap dengan foto, titik lokasi, status, dan riwayat tindak lanjut.
                </p>

                <div class="anim-fade-up anim-delay-3 mt-8 flex flex-wrap gap-3">
                    <a href="{{ $primaryUrl }}" class="group inline-flex items-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-brand-50">
                        {{ $primaryLabel }}
                        <svg class="h-4 w-4 text-brand-700 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="#cara-kerja" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        Lihat alurnya
                    </a>
                </div>

                <dl class="anim-fade-up anim-delay-4 mt-10 grid max-w-lg grid-cols-3 gap-6 border-t border-white/10 pt-6">
                    <div>
                        <dt class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Laporan tertangani</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white" style="font-family:var(--font-display)"><span data-count="1280">0</span>+</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Warga terdaftar</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white" style="font-family:var(--font-display)"><span data-count="640">0</span>+</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Blok terpantau</dt>
                        <dd class="mt-1 text-2xl font-semibold text-white" style="font-family:var(--font-display)"><span data-count="24">0</span></dd>
                    </div>
                </dl>
            </div>

            {{-- Panel peta --}}
            <div class="anim-fade-up anim-delay-3 relative mx-auto w-full max-w-lg lg:max-w-none">
                <div class="anim-float overflow-hidden rounded-xl border border-white/10 bg-slate-900/80 shadow-soft ring-1 ring-white/5 backdrop-blur">
                    <div class="flex items-center justify-between border-b border-white/10 px-4 py-2.5">
                        <div class="flex items-center gap-3">
                            <div class="flex gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-white/20"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-white/20"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-white/20"></span>
                            </div>
                            <span class="text-xs font-medium text-slate-400">Peta laporan · Blok A–D</span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-md bg-brand-500/15 px-2 py-1 text-[11px] font-semibold text-brand-300 ring-1 ring-inset ring-brand-400/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand-400"></span> Live
                        </span>
                    </div>
                    <div class="relative">
                        <img src="{{ asset('images/landing/aerial-2.jpg') }}" alt="Peta lingkungan perumahan" class="h-72 w-full object-cover opacity-90 sm:h-80">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/10 to-transparent"></div>

                        <span class="absolute left-[24%] top-[28%] flex h-4 w-4 items-center justify-center">
                            <span class="absolute h-4 w-4 rounded-full bg-signal-400 anim-pulse-ring"></span>
                            <span class="relative h-2.5 w-2.5 rounded-full border-2 border-slate-900 bg-signal-400"></span>
                        </span>
                        <span class="absolute left-[56%] top-[48%] flex h-4 w-4 items-center justify-center">
                            <span class="absolute h-4 w-4 rounded-full bg-sky-400 anim-pulse-ring" style="animation-delay:-1s"></span>
                            <span class="relative h-2.5 w-2.5 rounded-full border-2 border-slate-900 bg-sky-400"></span>
                        </span>
                        <span class="absolute left-[38%] top-[66%] flex h-4 w-4 items-center justify-center">
                            <span class="relative h-2.5 w-2.5 rounded-full border-2 border-slate-900 bg-brand-400"></span>
                        </span>

                        <div class="absolute inset-x-3 bottom-3 flex items-center justify-between gap-3 rounded-lg border border-white/10 bg-slate-900/90 px-3.5 py-3 backdrop-blur">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 items-center justify-center rounded-md bg-signal-500/15 text-signal-400 ring-1 ring-inset ring-signal-400/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-white">Lampu jalan mati</p>
                                    <p class="text-xs text-slate-400">Blok B · 5 menit lalu</p>
                                </div>
                            </div>
                            <span class="rounded-md bg-signal-500/15 px-2.5 py-1 text-[11px] font-semibold text-signal-300 ring-1 ring-inset ring-signal-400/20">Baru</span>
                        </div>
                    </div>
                </div>

                <div class="anim-float-slow absolute -left-5 top-20 hidden rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-card sm:block">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Status diperbarui</p>
                    <p class="mt-0.5 text-sm font-semibold text-brand-700">Diproses &rarr; Selesai</p>
                </div>
                <div class="anim-float absolute -right-3 bottom-20 hidden rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-card sm:block" style="animation-delay:-3s">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Rata-rata respons</p>
                    <p class="mt-0.5 text-sm font-semibold text-slate-900">&lt; 2 hari</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== STRIP ===================== --}}
    <div class="border-y border-slate-200 bg-white py-4">
        <div class="flex overflow-hidden" style="mask-image:linear-gradient(90deg,transparent,#000 10%,#000 90%,transparent);-webkit-mask-image:linear-gradient(90deg,transparent,#000 10%,#000 90%,transparent)">
            <ul class="anim-marquee flex shrink-0 items-center gap-8 pr-8 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                @php($keywords = ['Keamanan', 'Infrastruktur', 'Kebersihan', 'Penerangan Jalan', 'Saluran Air', 'Fasilitas Umum', 'Ketertiban', 'Lingkungan'])
                @foreach (array_merge($keywords, $keywords) as $word)
                    <li class="flex shrink-0 items-center gap-8">
                        <span>{{ $word }}</span>
                        <span class="h-1 w-1 rounded-full bg-brand-400"></span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ===================== FITUR ===================== --}}
    <section id="fitur" class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-24">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
            <div data-reveal>
                <p class="eyebrow">Fitur utama</p>
                <h2 class="mt-3 text-3xl font-semibold text-slate-900 sm:text-4xl" style="font-family:var(--font-display)">Alat yang dibutuhkan warga dan pengurus, tanpa kerumitan.</h2>
                <p class="mt-4 max-w-md text-slate-600">Setiap laporan punya lokasi, bukti, status, dan jejak komunikasi — supaya penanganan bisa dipantau, bukan ditebak.</p>
                <a href="{{ $primaryUrl }}" class="btn-primary mt-7">{{ $primaryLabel }}</a>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @php($fitur = [
                    ['Peta & titik lokasi', 'Setiap laporan bertitik koordinat tampil di peta dashboard dan diperbarui berkala.', 'M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z'],
                    ['Bukti foto & video', 'Lampirkan hingga beberapa berkas agar konteks masalah jelas bagi pengurus.', 'm2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
                    ['Status & notifikasi', 'Perubahan status (baru, diproses, selesai) tercatat dan dikirim lewat email.', 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0'],
                    ['Diskusi tindak lanjut', 'Warga dan pengurus berkomentar pada laporan sampai masalah dinyatakan selesai.', 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z'],
                ])
                @foreach ($fitur as $i => [$judul, $isi, $path])
                    <div data-reveal style="transition-delay:{{ $i * 70 }}ms" class="card group p-5 transition duration-200 hover:-translate-y-1 hover:border-brand-200">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white group-hover:ring-brand-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900" style="font-family:var(--font-display)">{{ $judul }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== ALUR ===================== --}}
    <section id="cara-kerja" class="border-y border-slate-200 bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <p class="eyebrow justify-center">Alur kerja</p>
                <h2 class="mt-3 text-3xl font-semibold text-slate-900 sm:text-4xl" style="font-family:var(--font-display)">Dari laporan masuk sampai selesai ditangani</h2>
                <p class="mt-4 text-slate-600">Proses ringkas dan tercatat, agar warga tahu posisi laporannya dan pengurus punya antrean yang jelas.</p>
            </div>

            <div class="relative mt-14 grid gap-5 md:grid-cols-3">
                <div class="pointer-events-none absolute left-[16%] right-[16%] top-10 hidden h-px bg-gradient-to-r from-transparent via-slate-300 to-transparent md:block"></div>
                @php($langkah = [
                    ['01', 'Kirim laporan', 'Pilih kategori, tandai lokasi di peta, tulis uraian, dan unggah bukti foto atau video.'],
                    ['02', 'Verifikasi & proses', 'RT/RW memeriksa laporan sesuai cakupan blok lalu memperbarui status penanganan.'],
                    ['03', 'Pantau & tutup', 'Warga menerima pembaruan status dan dapat berdiskusi sampai masalah dinyatakan selesai.'],
                ])
                @foreach ($langkah as $i => [$no, $judul, $isi])
                    <div data-reveal style="transition-delay:{{ $i * 100 }}ms" class="relative">
                        <div class="card h-full p-6">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white" style="font-family:var(--font-display)">{{ $no }}</span>
                            <h3 class="mt-5 text-lg font-semibold text-slate-900" style="font-family:var(--font-display)">{{ $judul }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $isi }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== PETA / SHOWCASE ===================== --}}
    <section id="peta" class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div data-reveal="left" class="relative">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white p-2 shadow-card">
                    <img src="{{ asset('images/landing/map-app.jpg') }}" alt="Tampilan peta laporan di perangkat" class="h-80 w-full rounded-lg object-cover">
                </div>
                <div class="anim-float absolute -bottom-5 -right-3 rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-card sm:-right-5">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">Hari ini</p>
                    <p class="mt-0.5 text-xl font-semibold text-brand-700" style="font-family:var(--font-display)"><span data-count="12">0</span> titik aktif</p>
                </div>
            </div>

            <div data-reveal="right">
                <p class="eyebrow">Peta komunitas</p>
                <h2 class="mt-3 text-3xl font-semibold text-slate-900 sm:text-4xl" style="font-family:var(--font-display)">Sebaran masalah terlihat, prioritas jadi jelas.</h2>
                <p class="mt-4 leading-relaxed text-slate-600">
                    Marker laporan muncul berdasarkan status dan kategori. Warga melihat kondisi lingkungan, pengurus memetakan area yang perlu ditangani lebih dulu.
                </p>
                <ul class="mt-6 space-y-3">
                    @foreach (['Marker diberi warna sesuai status penanganan', 'Cakupan akses per blok (RT) dan menyeluruh (RW)', 'Detail laporan dapat dibuka langsung dari titik peta'] as $point)
                        <li class="flex items-start gap-3 text-sm text-slate-700">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-brand-700 text-white">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-20 grid items-center gap-12 lg:grid-cols-2">
            <div data-reveal="left" class="order-2 lg:order-1">
                <p class="eyebrow">Tindak lanjut</p>
                <h2 class="mt-3 text-3xl font-semibold text-slate-900 sm:text-4xl" style="font-family:var(--font-display)">Transparan untuk warga, terukur untuk pengurus.</h2>
                <p class="mt-4 leading-relaxed text-slate-600">
                    Riwayat status dan komentar tersimpan pada setiap laporan, sehingga koordinasi tidak bergantung pada pesan yang tercecer.
                </p>
                <div class="mt-7 grid grid-cols-2 gap-4">
                    <div class="rounded-lg border border-slate-200 bg-mist-50 p-4">
                        <p class="text-3xl font-semibold text-slate-900" style="font-family:var(--font-display)"><span data-count="94">0</span>%</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">Laporan ditindaklanjuti</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-mist-50 p-4">
                        <p class="text-3xl font-semibold text-slate-900" style="font-family:var(--font-display)">&lt; <span data-count="2">0</span> hari</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">Rata-rata respons</p>
                    </div>
                </div>
            </div>
            <div data-reveal="right" class="order-1 lg:order-2">
                <div class="grid grid-cols-2 gap-4">
                    <img src="{{ asset('images/landing/community.jpg') }}" alt="Warga berdiskusi" class="h-64 w-full rounded-xl border border-slate-200 object-cover shadow-card">
                    <img src="{{ asset('images/landing/cleanup.jpg') }}" alt="Perbaikan lingkungan" class="mt-8 h-64 w-full rounded-xl border border-slate-200 object-cover shadow-card">
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== KATEGORI ===================== --}}
    <section class="border-y border-slate-200 bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal>
                <div class="max-w-xl">
                    <p class="eyebrow">Cakupan laporan</p>
                    <h2 class="mt-3 text-3xl font-semibold text-slate-900 sm:text-4xl" style="font-family:var(--font-display)">Kategori yang relevan untuk lingkungan hunian</h2>
                </div>
                <p class="max-w-sm text-sm text-slate-600">Daftar kategori dapat disesuaikan pengurus sesuai kebutuhan perumahan.</p>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @php($kategori = [
                    ['Keamanan', 'Gangguan ketertiban, aktivitas mencurigakan, atau insiden di lingkungan.', 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                    ['Infrastruktur', 'Jalan rusak, lampu mati, saluran air, dan fasilitas umum bermasalah.', 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z'],
                    ['Kebersihan', 'Sampah menumpuk, area umum kotor, atau gangguan kebersihan lain.', 'm14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0'],
                    ['Lingkungan', 'Pohon tumbang, genangan, polusi, dan isu lingkungan sekitar.', 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418'],
                ])
                @foreach ($kategori as $i => [$nama, $isi, $path])
                    <div data-reveal style="transition-delay:{{ $i * 70 }}ms" class="rounded-xl border border-slate-200 bg-mist-50 p-5 transition hover:border-brand-200 hover:bg-white">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-brand-700 ring-1 ring-inset ring-slate-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/></svg>
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-900" style="font-family:var(--font-display)">{{ $nama }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== TESTIMONI ===================== --}}
    <section id="testimoni" class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-24">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
            <p class="eyebrow justify-center">Pengalaman pengguna</p>
            <h2 class="mt-3 text-3xl font-semibold text-slate-900 sm:text-4xl" style="font-family:var(--font-display)">Dipakai warga, memudahkan pengurus</h2>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @php($testimoni = [
                ['avatar-1.jpg', 'Bu Sari', 'Warga Blok C', 'Saya bisa melapor dari ponsel dan langsung tahu saat statusnya berubah. Prosesnya jauh lebih jelas daripada mengirim pesan terpisah.'],
                ['avatar-2.jpg', 'Pak Dimas', 'Ketua RT', 'Peta membantu saya melihat titik mana yang paling sering bermasalah, jadi penanganan bisa diprioritaskan per blok.'],
                ['avatar-3.jpg', 'Mbak Ayu', 'Warga Blok A', 'Komentar di laporan membuat komunikasi dengan pengurus rapi dan tercatat, tidak tenggelam di grup chat.'],
            ])
            @foreach ($testimoni as $i => [$avatar, $nama, $peran, $isi])
                <figure data-reveal style="transition-delay:{{ $i * 90 }}ms" class="card flex h-full flex-col p-6">
                    <svg class="h-6 w-6 text-brand-200" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/></svg>
                    <blockquote class="mt-3 flex-1 text-sm leading-relaxed text-slate-700">{{ $isi }}</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 border-t border-slate-100 pt-4">
                        <img src="{{ asset('images/landing/' . $avatar) }}" alt="{{ $nama }}" class="h-10 w-10 rounded-lg object-cover ring-1 ring-slate-200">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $nama }}</p>
                            <p class="text-xs text-slate-500">{{ $peran }}</p>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6 sm:pb-24">
        <div data-reveal="zoom" class="relative overflow-hidden rounded-2xl border border-slate-800 bg-slate-950 px-6 py-14 text-center sm:px-12 sm:py-16">
            <div class="hero-grid pointer-events-none absolute inset-0 opacity-40"></div>
            <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 -right-16 h-64 w-64 rounded-full bg-signal-500/10 blur-3xl"></div>
            <div class="relative mx-auto max-w-2xl">
                <p class="eyebrow justify-center !text-brand-300">Mulai sekarang</p>
                <h2 class="mt-3 text-3xl font-semibold text-white sm:text-4xl" style="font-family:var(--font-display)">Jadikan laporan warga mudah dilacak dan ditindaklanjuti</h2>
                <p class="mt-4 text-slate-300">Daftar sebagai warga, kirim laporan pertama, dan pantau perkembangannya bersama pengurus lingkungan.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ $primaryUrl }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-brand-50">{{ $primaryLabel }}</a>
                    @if (! $isLoggedIn)
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/5 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Masuk</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="brand-mark">L</span>
                    <span class="text-lg font-semibold tracking-tight text-slate-900" style="font-family:var(--font-display)">Laporku</span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-600">Sistem pelaporan lingkungan perumahan untuk warga dan pengurus RT/RW — dari laporan, peta, hingga tindak lanjut.</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Navigasi</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="#fitur" class="font-medium text-slate-700 transition hover:text-brand-700">Fitur</a></li>
                    <li><a href="#cara-kerja" class="font-medium text-slate-700 transition hover:text-brand-700">Alur</a></li>
                    <li><a href="#peta" class="font-medium text-slate-700 transition hover:text-brand-700">Peta</a></li>
                    <li><a href="#testimoni" class="font-medium text-slate-700 transition hover:text-brand-700">Cerita</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Akses</p>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ route('login') }}" class="font-medium text-slate-700 transition hover:text-brand-700">Masuk</a></li>
                    <li><a href="{{ route('register') }}" class="font-medium text-slate-700 transition hover:text-brand-700">Daftar warga</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-100">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <p>&copy; {{ date('Y') }} Laporku. Sistem Pelaporan Lingkungan Perumahan.</p>
                <p>Foto ilustrasi: Pexels.</p>
            </div>
        </div>
    </footer>
@endsection
