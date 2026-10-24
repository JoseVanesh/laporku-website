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
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-bold text-white shadow-sm">L</span>
                <span class="nav-brand text-lg font-semibold tracking-tight">Laporku</span>
            </a>

            <div class="hidden items-center gap-1 md:flex">
                <a href="#fitur" class="nav-link rounded-lg px-3 py-2 text-sm font-medium transition">Fitur</a>
                <a href="#cara-kerja" class="nav-link rounded-lg px-3 py-2 text-sm font-medium transition">Cara Kerja</a>
                <a href="#peta" class="nav-link rounded-lg px-3 py-2 text-sm font-medium transition">Peta</a>
                <a href="#testimoni" class="nav-link rounded-lg px-3 py-2 text-sm font-medium transition">Testimoni</a>
            </div>

            <div class="flex items-center gap-2">
                @if ($isLoggedIn)
                    <a href="{{ route('dashboard') }}" class="nav-cta rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="nav-login rounded-lg px-3 py-2 text-sm font-medium transition">Masuk</a>
                    <a href="{{ route('register') }}" class="nav-cta rounded-lg px-4 py-2 text-sm font-semibold shadow-sm transition">Daftar</a>
                @endif
            </div>
        </nav>
    </header>

    {{-- ===================== HERO ===================== --}}
    <section class="relative flex min-h-[92vh] items-center overflow-hidden pt-24 pb-16 sm:pt-28">
        <img src="{{ asset('images/landing/hero-neighborhood.jpg') }}" alt="Perumahan dari udara"
             class="absolute inset-0 h-full w-full object-cover" loading="eager">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-950/92 via-brand-800/85 to-brand-600/70"></div>
        <div class="absolute -right-32 -top-24 h-96 w-96 rounded-full bg-brand-300/25 blur-3xl anim-blob"></div>
        <div class="absolute -bottom-32 left-1/4 h-96 w-96 rounded-full bg-white/10 blur-3xl anim-blob" style="animation-delay:-6s"></div>

        <div class="relative mx-auto grid w-full max-w-6xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2">
            <div class="text-white">
                <span class="anim-fade-up inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-widest text-brand-50 backdrop-blur">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-brand-300 anim-pulse-ring"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-200"></span>
                    </span>
                    Sistem Pelaporan Lingkungan
                </span>

                <h1 class="anim-fade-up anim-delay-1 mt-5 text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">
                    Lingkungan perumahan aman &amp; rapi, dimulai dari satu laporan.
                </h1>

                <p class="anim-fade-up anim-delay-2 mt-5 max-w-xl text-base leading-relaxed text-brand-50/90 sm:text-lg">
                    Laporkan gangguan keamanan, kerusakan fasilitas, atau masalah kebersihan lengkap dengan
                    foto dan titik lokasi. Pantau sebarannya di peta real-time dan tindak lanjuti bersama pengurus.
                </p>

                <div class="anim-fade-up anim-delay-3 mt-8 flex flex-wrap gap-3">
                    <a href="{{ $primaryUrl }}" class="group inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-soft transition hover:bg-brand-50">
                        {{ $primaryLabel }}
                        <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                    <a href="#peta" class="inline-flex items-center gap-2 rounded-xl border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        Lihat Peta Demo
                    </a>
                </div>

                <dl class="anim-fade-up anim-delay-4 mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-white/15 pt-6">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-brand-100/80">Laporan ditangani</dt>
                        <dd class="mt-1 text-2xl font-bold text-white"><span data-count="1280">0</span>+</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-brand-100/80">Warga aktif</dt>
                        <dd class="mt-1 text-2xl font-bold text-white"><span data-count="640">0</span>+</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-brand-100/80">Blok terpantau</dt>
                        <dd class="mt-1 text-2xl font-bold text-white"><span data-count="24">0</span></dd>
                    </div>
                </dl>
            </div>

            {{-- Mock peta --}}
            <div class="anim-fade-up anim-delay-3 relative mx-auto w-full max-w-md lg:max-w-none">
                <div class="anim-float rounded-3xl border border-white/20 bg-white/95 p-3 shadow-soft backdrop-blur">
                    <div class="flex items-center justify-between px-2 py-1.5">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-brand-400"></span>
                        </div>
                        <span class="text-xs font-medium text-slate-400">laporku.app/dashboard</span>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl">
                        <img src="{{ asset('images/landing/aerial-2.jpg') }}" alt="Peta laporan" class="h-64 w-full object-cover sm:h-72">
                        <div class="absolute inset-0 bg-brand-900/10"></div>

                        <span class="absolute left-[22%] top-[30%] flex h-4 w-4 items-center justify-center">
                            <span class="absolute h-4 w-4 rounded-full bg-amber-400 anim-pulse-ring"></span>
                            <span class="relative h-3 w-3 rounded-full border-2 border-white bg-amber-400"></span>
                        </span>
                        <span class="absolute left-[58%] top-[52%] flex h-4 w-4 items-center justify-center">
                            <span class="absolute h-4 w-4 rounded-full bg-sky-400 anim-pulse-ring" style="animation-delay:-1s"></span>
                            <span class="relative h-3 w-3 rounded-full border-2 border-white bg-sky-400"></span>
                        </span>
                        <span class="absolute left-[35%] top-[70%] flex h-4 w-4 items-center justify-center">
                            <span class="relative h-3 w-3 rounded-full border-2 border-white bg-brand-500"></span>
                        </span>

                        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between rounded-xl bg-white/95 px-3.5 py-2.5 shadow-card">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 text-brand-700">📍</span>
                                <div>
                                    <p class="text-xs font-semibold text-slate-800">Lampu jalan mati</p>
                                    <p class="text-[11px] text-slate-500">Blok B &middot; 5 menit lalu</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700">Baru</span>
                        </div>
                    </div>
                </div>

                <div class="anim-float-slow absolute -left-6 top-24 hidden rounded-2xl border border-white/20 bg-white px-4 py-3 shadow-soft sm:block">
                    <p class="text-[11px] font-medium text-slate-500">Status diperbarui</p>
                    <p class="text-sm font-semibold text-brand-700">Diproses ✔</p>
                </div>
                <div class="anim-float absolute -right-4 bottom-16 hidden rounded-2xl border border-white/20 bg-white px-4 py-3 shadow-soft sm:block" style="animation-delay:-3s">
                    <p class="text-[11px] font-medium text-slate-500">Notifikasi email</p>
                    <p class="text-sm font-semibold text-slate-800">Laporan selesai</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== MARQUEE ===================== --}}
    <div class="border-y border-brand-100 bg-mist-50 py-5">
        <div class="flex overflow-hidden" style="mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent);-webkit-mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent)">
            <ul class="anim-marquee flex shrink-0 items-center gap-10 pr-10 text-sm font-semibold uppercase tracking-widest text-slate-400">
                @php($keywords = ['Keamanan', 'Infrastruktur', 'Kebersihan', 'Lingkungan', 'Penerangan Jalan', 'Saluran Air', 'Taman & Fasilitas', 'Ketertiban'])
                @foreach (array_merge($keywords, $keywords) as $word)
                    <li class="flex shrink-0 items-center gap-10">
                        <span>{{ $word }}</span>
                        <span class="text-brand-300">&bull;</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ===================== FITUR ===================== --}}
    <section id="fitur" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
            <p class="text-sm font-semibold uppercase tracking-widest text-brand-500">Fitur Utama</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Semua yang warga butuhkan untuk melapor</h2>
            <p class="mt-4 text-slate-600">Dari membuat laporan sampai memantau penyelesaiannya, semuanya dalam satu tempat.</p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @php($fitur = [
                ['🗺️', 'Peta Real-time', 'Setiap laporan bertitik lokasi tampil live di peta komunitas.', 'from-brand-100 to-brand-50'],
                ['📸', 'Bukti Foto & Video', 'Lampirkan hingga 6 berkas foto atau video agar laporan jelas.', 'from-amber-100 to-amber-50'],
                ['🔔', 'Notifikasi Status', 'Email otomatis setiap kali status laporan Anda berubah.', 'from-sky-100 to-sky-50'],
                ['💬', 'Diskusi Dua Arah', 'Berkomentar dengan pengurus sampai masalah tuntas.', 'from-rose-100 to-rose-50'],
            ])
            @foreach ($fitur as $i => [$ikon, $judul, $isi, $grad])
                <div data-reveal="zoom" style="transition-delay:{{ $i * 80 }}ms"
                     class="card group p-6 transition duration-300 hover:-translate-y-1.5 hover:border-brand-200 hover:shadow-soft">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $grad }} text-2xl transition group-hover:scale-110">{{ $ikon }}</div>
                    <h3 class="mt-4 font-semibold text-slate-900">{{ $judul }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $isi }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===================== CARA KERJA ===================== --}}
    <section id="cara-kerja" class="bg-mist-50 py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <p class="text-sm font-semibold uppercase tracking-widest text-brand-500">Cara Kerja</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Tiga langkah, masalah tertangani</h2>
            </div>

            <div class="relative mt-14 grid gap-6 md:grid-cols-3">
                <div class="pointer-events-none absolute left-0 right-0 top-9 hidden md:block">
                    <div class="mx-auto h-px w-2/3 border-t-2 border-dashed border-brand-200"></div>
                </div>

                @php($langkah = [
                    ['1', 'Buat Laporan', 'Pilih jenis pelaporan, tandai lokasi di peta, tulis uraian, dan unggah bukti foto atau video.'],
                    ['2', 'Verifikasi Pengurus', 'Admin atau RT/RW memeriksa laporan lalu memperbarui status penanganannya.'],
                    ['3', 'Pantau Sampai Selesai', 'Terima notifikasi email tiap perubahan status dan berdiskusi lewat komentar.'],
                ])
                @foreach ($langkah as $i => [$no, $judul, $isi])
                    <div data-reveal style="transition-delay:{{ $i * 120 }}ms" class="relative">
                        <div class="card relative h-full p-6">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-600 text-lg font-bold text-white shadow-soft">{{ $no }}</span>
                            <h3 class="mt-5 font-semibold text-slate-900">{{ $judul }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $isi }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== SHOWCASE ===================== --}}
    <section id="peta" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
        <div class="grid items-center gap-10 lg:grid-cols-2">
            <div data-reveal="left" class="relative order-2 lg:order-1">
                <div class="overflow-hidden rounded-3xl border border-brand-100 shadow-card">
                    <img src="{{ asset('images/landing/map-app.jpg') }}" alt="Peta laporan di ponsel" class="h-80 w-full object-cover">
                </div>
                <div class="anim-float absolute -bottom-6 -right-4 hidden rounded-2xl border border-brand-100 bg-white px-5 py-4 shadow-soft sm:block">
                    <p class="text-2xl font-bold text-brand-600"><span data-count="12">0</span></p>
                    <p class="text-xs font-medium text-slate-500">titik aktif hari ini</p>
                </div>
            </div>

            <div data-reveal="right" class="order-1 lg:order-2">
                <p class="text-sm font-semibold uppercase tracking-widest text-brand-500">Peta Komunitas</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Lihat kondisi lingkungan dalam satu pandangan</h2>
                <p class="mt-4 leading-relaxed text-slate-600">
                    Setiap laporan yang disertai titik lokasi muncul di peta dashboard dan diperbarui otomatis.
                    Warga melihat sebarannya, pengurus fokus pada area yang perlu ditangani lebih dulu.
                </p>
                <ul class="mt-6 space-y-3">
                    @foreach (['Marker berwarna sesuai status laporan', 'Cakupan per blok untuk RT/RW, menyeluruh untuk admin', 'Klik marker untuk melihat detail laporan'] as $point)
                        <li class="flex items-start gap-3 text-sm text-slate-700">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
                <a href="{{ $primaryUrl }}" class="btn-primary mt-7">{{ $primaryLabel }}</a>
            </div>
        </div>

        <div class="mt-24 grid items-center gap-10 lg:grid-cols-2">
            <div data-reveal="left">
                <p class="text-sm font-semibold uppercase tracking-widest text-brand-500">Gotong Royong</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Dari laporan menjadi aksi bersama</h2>
                <p class="mt-4 leading-relaxed text-slate-600">
                    Laporan yang transparan memudahkan warga dan pengurus bergotong royong. Pantau riwayat status,
                    diskusikan solusinya, lalu tandai selesai ketika masalah sudah ditangani.
                </p>
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-mist-50 p-4 ring-1 ring-inset ring-brand-100">
                        <p class="text-2xl font-bold text-slate-900"><span data-count="94">0</span>%</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">laporan ditindaklanjuti</p>
                    </div>
                    <div class="rounded-2xl bg-mist-50 p-4 ring-1 ring-inset ring-brand-100">
                        <p class="text-2xl font-bold text-slate-900">&lt; <span data-count="2">0</span> hari</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">rata-rata respons pengurus</p>
                    </div>
                </div>
            </div>

            <div data-reveal="right" class="relative">
                <div class="grid grid-cols-2 gap-4">
                    <img src="{{ asset('images/landing/community.jpg') }}" alt="Warga bergotong royong" class="h-64 w-full rounded-3xl border border-brand-100 object-cover shadow-card">
                    <img src="{{ asset('images/landing/cleanup.jpg') }}" alt="Aksi bersih lingkungan" class="mt-8 h-64 w-full rounded-3xl border border-brand-100 object-cover shadow-card">
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== KATEGORI ===================== --}}
    <section class="bg-mist-50 py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <p class="text-sm font-semibold uppercase tracking-widest text-brand-500">Jenis Pelaporan</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Laporkan apa pun yang mengganggu</h2>
                <p class="mt-4 text-slate-600">Daftar jenis pelaporan dapat dikonfigurasi oleh admin sesuai kebutuhan perumahan.</p>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @php($kategori = [
                    ['🛡️', 'Keamanan', 'Pencurian, orang mencurigakan, gangguan ketertiban.'],
                    ['🛣️', 'Infrastruktur', 'Jalan rusak, lampu mati, saluran air tersumbat.'],
                    ['🧹', 'Kebersihan', 'Sampah menumpuk, got kotor, area umum kotor.'],
                    ['🌳', 'Lingkungan', 'Pohon tumbang, genangan, polusi, dan lainnya.'],
                ])
                @foreach ($kategori as $i => [$ikon, $nama, $isi])
                    <div data-reveal style="transition-delay:{{ $i * 80 }}ms"
                         class="card p-5 transition hover:-translate-y-1.5 hover:border-brand-200 hover:shadow-soft">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-2xl">{{ $ikon }}</div>
                        <h3 class="mt-4 font-semibold text-slate-900">{{ $nama }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== TESTIMONI ===================== --}}
    <section id="testimoni" class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
            <p class="text-sm font-semibold uppercase tracking-widest text-brand-500">Testimoni</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Dipercaya warga dan pengurus</h2>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @php($testimoni = [
                ['avatar-1.jpg', 'Bu Sari', 'Warga Blok C', 'Lampu jalan depan rumah saya mati, saya lapor malam dan besoknya sudah diganti. Prosesnya jelas dan bisa dipantau.'],
                ['avatar-2.jpg', 'Pak Dimas', 'Ketua RT/RW', 'Peta laporan sangat membantu. Saya langsung tahu titik mana yang butuh perhatian dari blok yang saya pegang.'],
                ['avatar-3.jpg', 'Mbak Ayu', 'Warga Blok A', 'Fitur komentar bikin komunikasi dengan pengurus jadi rapi. Tidak perlu lagi tanya-tanya di grup yang penuh chat.'],
            ])
            @foreach ($testimoni as $i => [$avatar, $nama, $peran, $isi])
                <figure data-reveal style="transition-delay:{{ $i * 100 }}ms" class="card flex h-full flex-col p-6">
                    <div class="flex gap-1 text-amber-400">
                        @for ($s = 0; $s < 5; $s++)
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L10 15.9 4.8 17.6l1-5.8L1.5 7.7l5.9-.9L10 1.5z"/></svg>
                        @endfor
                    </div>
                    <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-slate-700">&ldquo;{{ $isi }}&rdquo;</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3 border-t border-brand-50 pt-4">
                        <img src="{{ asset('images/landing/' . $avatar) }}" alt="{{ $nama }}" class="h-10 w-10 rounded-full object-cover ring-2 ring-brand-100">
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
    <section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
        <div data-reveal="zoom" class="relative overflow-hidden rounded-3xl px-6 py-16 text-center shadow-soft sm:px-12">
            <img src="{{ asset('images/landing/park.jpg') }}" alt="Taman lingkungan" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-800/95 via-brand-700/90 to-brand-600/80"></div>
            <div class="relative mx-auto max-w-2xl text-white">
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Siap membuat lingkungan Anda lebih baik?</h2>
                <p class="mt-4 text-brand-50/90">Gabung bersama warga lain. Cukup daftar sekali, lalu mulai melapor dan memantau.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ $primaryUrl }}" class="rounded-xl bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50">{{ $primaryLabel }}</a>
                    @if (! $isLoggedIn)
                        <a href="{{ route('login') }}" class="rounded-xl border border-white/40 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Masuk</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="border-t border-brand-100 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-10 sm:px-6 md:flex-row md:items-center md:justify-between">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-sm font-bold text-white shadow-sm">L</span>
                    <span class="text-lg font-semibold tracking-tight text-slate-900">Laporku</span>
                </a>
                <p class="mt-3 max-w-sm text-sm text-slate-500">Sistem pelaporan lingkungan perumahan. Dilaporkan oleh warga, ditindaklanjuti oleh pengurus.</p>
            </div>
            <div class="flex flex-wrap gap-x-8 gap-y-2 text-sm">
                <a href="#fitur" class="font-medium text-slate-600 transition hover:text-brand-700">Fitur</a>
                <a href="#cara-kerja" class="font-medium text-slate-600 transition hover:text-brand-700">Cara Kerja</a>
                <a href="#peta" class="font-medium text-slate-600 transition hover:text-brand-700">Peta</a>
                <a href="{{ route('login') }}" class="font-medium text-slate-600 transition hover:text-brand-700">Masuk</a>
            </div>
        </div>
        <div class="border-t border-brand-50">
            <p class="mx-auto max-w-6xl px-4 py-5 text-xs text-slate-400 sm:px-6">&copy; {{ date('Y') }} Laporku. Foto oleh Pexels.</p>
        </div>
    </footer>
@endsection
