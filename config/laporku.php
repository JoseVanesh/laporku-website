<?php

return [

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

];
