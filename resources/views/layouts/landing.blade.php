<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') &middot; Laporku</title>
    <meta name="description" content="Laporku — sistem pelaporan lingkungan perumahan. Laporkan masalah, pantau sebarannya di peta, dan tindak lanjuti bersama pengurus RT/RW.">
    @include('partials.firebase-config')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-mist-50 font-sans text-slate-900 antialiased">
    @yield('content')
    @stack('scripts')
</body>
</html>
