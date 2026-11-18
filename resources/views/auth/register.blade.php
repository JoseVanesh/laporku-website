@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Daftar akun warga</h1>
    <p class="mt-1 text-sm text-slate-500">Buat akun untuk mulai melapor dan memantau lingkungan.</p>

    <div id="auth-error" class="mt-5 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>
    <div id="auth-success" class="mt-5 hidden rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"></div>

    <form id="register-form" class="mt-6 space-y-4" data-otp-send-url="{{ route('register.otp.send') }}" data-otp-verify-url="{{ route('register.otp.verify') }}" data-register-url="{{ route('register.store') }}">
        <div id="register-step-form" class="space-y-4">
            <div>
                <label for="nama" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required minlength="3" maxlength="100" placeholder="Nama sesuai KTP"
                       class="field mt-1.5">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="nama@email.com"
                       class="field mt-1.5">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
                <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password"
                       placeholder="Minimal 6 karakter" class="field mt-1.5">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="blok" class="block text-sm font-medium text-slate-700">Blok</label>
                    <input type="text" id="blok" name="blok" required maxlength="20" placeholder="mis. A" class="field mt-1.5">
                </div>
                <div>
                    <label for="rumah" class="block text-sm font-medium text-slate-700">No. Rumah</label>
                    <input type="text" id="rumah" name="rumah" maxlength="20" placeholder="mis. 12" class="field mt-1.5">
                </div>
            </div>

            <div>
                <label for="telepon" class="block text-sm font-medium text-slate-700">No. WhatsApp</label>
                <input type="tel" id="telepon" name="telepon" required minlength="9" maxlength="20" placeholder="08xxxxxxxxxx" class="field mt-1.5">
            </div>

            <button type="submit" id="send-otp-btn" class="btn-primary w-full">
                Kirim Kode Verifikasi
            </button>
        </div>

        <div id="register-step-otp" class="hidden space-y-4">
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                Kami mengirim kode 6 digit ke <span id="otp-email-label" class="font-semibold text-slate-900"></span>.
                <span id="otp-dev-hint" class="hidden mt-2 block text-xs text-slate-500">Mode lokal (MAIL_MAILER=log): cek kode di <code>storage/logs/laravel.log</code>.</span>
            </div>

            <div>
                <label for="otp-code" class="block text-sm font-medium text-slate-700">Kode Verifikasi</label>
                <input type="text" id="otp-code" name="otp_code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" placeholder="Masukkan 6 digit kode"
                       class="field mt-1.5 tracking-[0.3em] text-center text-lg">
            </div>

            <button type="button" id="verify-otp-btn" class="btn-primary w-full">
                Verifikasi &amp; Buat Akun
            </button>

            <div class="flex items-center justify-between gap-3 text-sm">
                <button type="button" id="resend-otp-btn" class="font-medium text-brand-600 hover:text-brand-500 disabled:cursor-not-allowed disabled:opacity-50">
                    Kirim ulang kode
                </button>
                <button type="button" id="change-email-btn" class="font-medium text-slate-500 hover:text-slate-700">
                    Ubah email/data
                </button>
            </div>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-500">Masuk</a>
    </p>
@endsection
