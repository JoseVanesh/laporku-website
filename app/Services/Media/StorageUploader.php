<?php

declare(strict_types=1);

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageUploader
{
    /**
     * Simpan berkas ke penyimpanan lokal server dan kembalikan URL publiknya.
     *
     * @return array{path: string, url: string, mime: string, size: int}
     */
    public function upload(UploadedFile $file, string $folder = 'laporan'): array
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $path = $folder.'/'.now()->format('Ymd').'/'.Str::random(20).'.'.$extension;

        Storage::disk($this->disk())->put($path, $file->get());

        return [
            'path' => $path,
            'url' => Storage::disk($this->disk())->url($path),
            'mime' => $file->getClientMimeType() ?: 'application/octet-stream',
            'size' => (int) $file->getSize(),
        ];
    }

    public function delete(string $path): void
    {
        Storage::disk($this->disk())->delete($path);
    }

    private function disk(): string
    {
        return (string) config('laporku.media_disk', 'public');
    }
}
