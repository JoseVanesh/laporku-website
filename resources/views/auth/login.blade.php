@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <h1 class="text-2xl font-bold text-slate-900">Masuk ke akun Anda</h1>
    <p class="mt-1 text-sm text-slate-500">Gunakan email dan kata sandi yang terdaftar.</p>

    <div id="auth-error" class="mt-5 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

    <form id="login-form" class="mt-6 space-y-4">
        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input type="email" id="email" name="email" required autocomplete="email" placeholder="nama@email.com"
                   class="field mt-1.5">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi</label>
            <input type="password" id="password" name="password" required autocomplete="current-password" minlength="6" placeholder="••••••••"
                   class="field mt-1.5">
        </div>

        <button type="submit" class="btn-primary w-full">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-500">Daftar sekarang</a>
    </p>
@endsection
