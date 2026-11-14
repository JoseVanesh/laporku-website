<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageUploader
{
    /**
     * Simpan berkas ke penyimpanan (lokal atau Cloudinary) dan kembalikan URL publiknya.
     *
     * @return array{path: string, url: string, mime: string, size: int}
     */
    public function upload(UploadedFile $file, string $folder = 'laporan'): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $path = $folder.'/'.now()->format('Ymd').'/'.Str::random(20).'.'.$extension;
        $mime = (string) ($file->getClientMimeType() ?: 'application/octet-stream');

        $this->put($path, (string) $file->get(), $mime);

        return [
            'path' => $path,
            'url' => $this->publicUrl($path, $mime),
            'mime' => $mime,
            'size' => (int) $file->getSize(),
        ];
    }

    public function put(string $path, string $contents, string $mime = 'application/octet-stream'): void
    {
        Storage::disk($this->disk())->put($this->storageId($path, $mime), $contents);
    }

    public function delete(string $path, string $mime = 'application/octet-stream'): void
    {
        Storage::disk($this->disk())->delete($this->storageId($path, $mime));
    }

    /**
     * URL publik untuk sebuah path di disk media aktif.
     *
     * Untuk Cloudinary URL dibangun manual agar formatnya selalu
     * https://res.cloudinary.com/{cloud}/{resource_type}/upload/{path}
     * tanpa ekstensi ganda dan tanpa memanggil API saat render halaman.
     */
    public function publicUrl(string $path, string $mime): string
    {
        $disk = $this->disk();
        $path = ltrim($path, '/');

        if ($disk !== 'cloudinary') {
            return Storage::disk($disk)->url($path);
        }

        $cloud = (string) config('filesystems.disks.cloudinary.cloud_name', '');
        $resourceType = $this->resourceType($mime);

        return sprintf(
            'https://res.cloudinary.com/%s/%s/upload/%s',
            $cloud,
            $resourceType,
            $this->storageId($path, $mime),
        );
    }

    /**
     * ID penyimpanan di disk aktif.
     *
     * Cloudinary: image/video disimpan tanpa ekstensi karena ia menentukan
     * format dari konten. Jika ekstensi ikut disimpan, URL pengiriman menjadi
     * dobel (file.png → file.png.png) dan menghasilkan 404.
     * Raw tetap menyimpan ekstensi agar formatnya jelas.
     */
    private function storageId(string $path, string $mime): string
    {
        $path = ltrim($path, '/');

        if ($this->disk() !== 'cloudinary' || $this->resourceType($mime) === 'raw') {
            return $path;
        }

        return preg_replace('/\.[^.\/]+$/', '', $path) ?? $path;
    }

    private function resourceType(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            default => 'raw',
        };
    }

    private function disk(): string
    {
        return (string) config('laporku.media_disk', 'public');
    }
}
