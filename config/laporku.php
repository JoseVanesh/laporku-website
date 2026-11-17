<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Peran Pengguna
    |--------------------------------------------------------------------------
    |
    | Label peran dipakai di navigasi, badge komentar, dan form pengguna.
    |
    */

    'roles' => [
        'warga' => 'Warga',
        'rt' => 'RT',
        'rw' => 'RW',
        'super_admin' => 'Super Admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin Awal
    |--------------------------------------------------------------------------
    |
    | Daftar email (dipisah koma) yang otomatis diangkat menjadi super admin
    | ketika login. Set lewat LAPORKU_SUPER_ADMIN_EMAILS di .env / Vercel.
    |
    */

    'super_admins' => array_values(array_filter(array_map(
        static fn (string $email): string => mb_strtolower(trim($email)),
        explode(',', (string) env('LAPORKU_SUPER_ADMIN_EMAILS', '')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Disk Penyimpanan Media Laporan
    |--------------------------------------------------------------------------
    |
    | Disk Laravel yang dipakai untuk menyimpan foto/video laporan warga.
    | Default "public" = storage/app/public (diakses lewat public/storage).
    | Bisa diubah ke "s3" atau disk lain bila nanti pindah ke object storage.
    |
    */

    'media_disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Peta Dashboard
    |--------------------------------------------------------------------------
    |
    | Titik tengah awal peta (lintang/bujur) dan tingkat zoom. Default menunjuk
    | ke tengah Indonesia; sesuaikan ke lokasi perumahan Anda lewat file .env.
    |
    */

    'map' => [
        'center' => [
            'lat' => (float) env('LAPORKU_MAP_LAT', -6.2),
            'lng' => (float) env('LAPORKU_MAP_LNG', 106.8166),
        ],
        'zoom' => (int) env('LAPORKU_MAP_ZOOM', 12),
    ],

    'cloudinary' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    ],

];
