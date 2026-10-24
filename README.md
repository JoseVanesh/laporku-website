# Laporku — Sistem Pelaporan Lingkungan Perumahan

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![Firebase](https://img.shields.io/badge/Firebase-Firestore%20%2B%20Auth-FFCA28?logo=firebase&logoColor=black)](https://firebase.google.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#lisensi)

Website pelaporan berbasis **Laravel 13** dengan **Firebase** (Firestore sebagai database,
Firebase Auth untuk login). Foto/video laporan disimpan di **server lokal** (tanpa Firebase
Storage agar tidak perlu paket berbayar/Blaze).

> **Demo:** tambahkan URL demo di sini bila sudah di-deploy.
>
> **Login demo:** tambahkan akun demo (email / password) di sini bila tersedia.

## Fitur

- **Warga** — daftar & login, membuat laporan (pilih jenis: Keamanan, Kebersihan, Infrastruktur,
  Lingkungan, dll), unggah foto/video, memantau status, berkomentar.
- **Admin & RT/RW** — memantau semua laporan, filter status/jenis/blok, mengubah status
  (Baru → Diproses → Selesai/Ditolak), membalas komentar.
  **RT/RW hanya melihat laporan di bloknya.**
- **Admin** — manajemen peran pengguna (warga / RT-RW / admin) dan jenis pelaporan.
- **Notifikasi email** otomatis ke pemilik laporan saat status berubah.

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Database & Auth | Firebase Firestore, Firebase Authentication (Email/Password) |
| Frontend | Blade, Tailwind CSS 4, Vite 8, JavaScript |
| Peta | Leaflet |
| Media | Penyimpanan lokal Laravel (`storage/app/public`) |
| Notifikasi | Email (Laravel Notifications) |

## Kebutuhan

| Komponen | Versi |
|---|---|
| PHP | **8.3+** |
| Composer | 2.x |
| Node.js | 18+ (untuk Tailwind & Firebase JS SDK) |

> **Ekstensi `grpc` TIDAK diperlukan.** Firestore di proyek ini dipaksa memakai transport
> **REST** (lihat `app/Providers/AppServiceProvider.php`) karena `php_grpc` di Windows
> menyebabkan crash (`0xC0000005` / access violation). Jika ekstensi grpc aktif tapi Firestore
> tetap error, nonaktifkan saja — REST akan dipakai otomatis.

## Cara Setup

### 1. Instalasi dependensi

```sh
composer install
npm install
cp .env.example .env   # jika .env belum ada
php artisan key:generate
php artisan storage:link   # agar foto/video laporan bisa diakses lewat /storage
```

### 2. Buat proyek Firebase

1. Buka [console.firebase.google.com](https://console.firebase.google.com) → **Add project**.
2. **Build > Authentication > Get started** → tab **Sign-in method** → aktifkan **Email/Password**.
3. **Build > Firestore Database > Create database** → pilih *Production mode* → region
   `asia-southeast2 (Jakarta)` atau terdekat.
4. **Project Settings (gear) > General > Your apps** → pilih platform **Web (</>)** → daftarkan app,
   salin nilai konfigurasi SDK (apiKey, authDomain, dll) untuk langkah 3.
5. **Project Settings > Service accounts > Generate new private key** → file JSON terunduh.
   Simpan sebagai:
   ```
   storage/app/firebase-service-account.json
   ```
   (file ini masuk `.gitignore` — jangan di-commit)

> **Firebase Storage tidak dipakai.** Sejak 2024 Storage mewajibkan paket Blaze (kartu kredit).
> Foto/video laporan disimpan di `storage/app/public/laporan` dan diakses lewat `/storage`.
> Disk-nya bisa diganti di `.env` (`MEDIA_DISK`), misal ke S3, tanpa mengubah kode.

### 3. Isi `.env`

```dotenv
# Firebase Admin SDK (server)
FIREBASE_CREDENTIALS=storage/app/firebase-service-account.json

# Firebase Web SDK (JavaScript browser)
VITE_FIREBASE_API_KEY=AIza...
VITE_FIREBASE_AUTH_DOMAIN=projek-anda.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=projek-anda
VITE_FIREBASE_MESSAGING_SENDER_ID=1234567890
VITE_FIREBASE_APP_ID=1:1234567890:web:abcdef

# Penyimpanan media (foto/video) — default server lokal
MEDIA_DISK=public
```

Setelah mengubah `VITE_*`, jalankan ulang `npm run dev` (atau `npm run build`).

### 4. Pasang Security Rules & Index

Cara termudah — pakai Firebase CLI (folder project sudah berisi `firebase.json`,
`.firebaserc`, dan folder `firebase/`):

```sh
npm install -g firebase-tools   # bila belum ada
firebase login
firebase deploy --only firestore   # rules + composite index
```

Cara manual — salin isi file ke konsol:

| File | Isi ke |
|---|---|
| `firebase/firestore.rules` | Firestore > **Rules** |
| `firebase/firestore.indexes.json` | Firestore > **Indexes** (tab Composite) |

**Index komposit wajib dipasang.** Jika muncul error *"The query requires an index"*,
buka link yang tertera di pesan error untuk membuat index tersebut, atau deploy semua index
dari `firebase/firestore.indexes.json` sekaligus.

### 5. Siapkan data awal

```sh
php artisan firebase:seed-categories    # membuat 5 jenis pelaporan default
php artisan firebase:make-admin email@anda.com   # jadikan diri admin (setelah sekali register)
```

Alur peran:

1. Register biasa lewat website → peran **warga**.
2. Jalankan `firebase:make-admin` untuk akun Anda → peran **admin**.
3. Di menu **Pengguna**, ubah peran warga lain menjadi **RT/RW** dan isi **blok**-nya
   (blok dipakai untuk membatasi laporan yang bisa dilihat RT/RW).
4. Pengguna harus **logout lalu login lagi** agar peran baru berlaku.

### 6. Jalankan

```sh
php artisan serve          # http://localhost:8000
npm run dev                # Vite dev server (Tailwind + JS)
```

Untuk produksi:

```sh
npm run build
```

### Menjalankan tes

```sh
php artisan test            # tes dasar (tanpa Firebase)
```

`tests/Feature/PagesSmokeTest.php` adalah tes smoke yang menembak Firestore sungguhan
(memverifikasi index + semua halaman). Tes ini dilewati secara default; jalankan dengan:

```sh
$env:LAPORKU_SMOKE_EMAIL='email@admin.com'; $env:LAPORKU_LIVE_SMOKE='true'; php artisan test
```

(Ubuntu/macOS: `LAPORKU_SMOKE_EMAIL=... LAPORKU_LIVE_SMOKE=true php artisan test`)

### Email (notifikasi status)

Default `MAIL_MAILER=log` (hasil email ditulis ke `storage/logs/laravel.log`).
Untuk email sungguhan isi `MAIL_*` di `.env`, misal [Mailtrap](https://mailtrap.io)
(lingkungan dev) atau SMTP perumahan/layanan Anda.

## Struktur Penting

```
app/Http/Middleware/      EnsureFirebaseUser (wajib login), EnsureRole (role:admin,rt)
app/Http/Controllers/     AuthController, LaporanController, Admin/*
app/Services/Firestore/   UserStore, ReportStore, CategoryStore (akses Firestore)
app/Services/Media/       StorageUploader (simpan foto/video ke server lokal)
app/Notifications/        StatusLaporanBerubah (email ke warga)
app/Console/Commands/     firebase:seed-categories, firebase:make-admin
resources/views/          layouts, auth, warga/*, admin/*
firebase/                 rules + index untuk Firebase Console
```

## Kumpulan Data Firestore

```
users/{uid}                 nama, email, telepon, role(warga|rt|admin), blok, rumah, created_at
reports/{id}                uid_penulis, nama_penulis, email_penulis, judul, kategori, deskripsi,
                            lokasi, blok, status, media[{url,tipe,mime,size,path}], created_at, updated_at
reports/{id}/comments/{id}  uid, nama, peran, isi, sistem(bool), created_at
categories/{id}             nama, created_at
```

## Catatan Teknis

- **Upload via server** — foto/video melewati Laravel lalu disimpan ke `storage/app/public`
  (batas 50 MB/file, maks 6 file). Wajib `php artisan storage:link` sekali. Pastikan
  `upload_max_filesize` & `post_max_size` di `php.ini` cukup (Laragon default sudah 2G).
- **Sesi login** disimpan di Laravel (session database/sqlite), identitas diverifikasi dari
  ID token Firebase Auth. Data aplikasi tetap sepenuhnya di Firebase.
- **Firestore Security Rules** menolak semua akses client karena hanya server (Admin SDK)
  yang membaca/menulis. Media laporan boleh diakses publik lewat URL berkas yang acak.
- **Transport Firestore = REST.** `app/Providers/AppServiceProvider.php` mengikat
  `FirestoreClient` dengan `['transport' => 'rest']` untuk menghindari crash ekstensi grpc di
  Windows. Karena itu ekstensi grpc boleh dimatikan.

## Lisensi

Proyek ini dirilis di bawah [MIT License](LICENSE).

## Penulis

**Jose Vanesh** — [github.com/JoseVanesh](https://github.com/JoseVanesh)
