<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Media\StorageUploader;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateMediaToCloudinary extends Command
{
    protected $signature = 'firebase:migrate-media {--dry-run : Tanpa mengunggah atau menulis ke Firestore}';

    protected $description = 'Mengunggah ulang media laporan lokal ke Cloudinary dan memperbarui URL di Firestore';

    public function handle(FirestoreClient $db, StorageUploader $uploader): int
    {
        if ((string) config('laporku.media_disk') !== 'cloudinary') {
            $this->error('MEDIA_DISK belum "cloudinary". Set MEDIA_DISK=cloudinary lalu jalankan lagi.');

            return self::FAILURE;
        }

        $cloud = (string) config('filesystems.disks.cloudinary.cloud_name');
        if ($cloud === '') {
            $this->error('CLOUDINARY_CLOUD_NAME belum diisi di .env.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $uploaded = 0;
        $updated = 0;
        $skipped = 0;
        $missing = 0;

        foreach ($db->collection('reports')->documents() as $snapshot) {
            if (! $snapshot->exists()) {
                continue;
            }

            $data = $snapshot->data();
            $media = $data['media'] ?? [];

            if (! is_array($media) || $media === []) {
                continue;
            }

            $changed = false;

            foreach ($media as $i => $item) {
                $url = (string) ($item['url'] ?? '');
                $path = (string) ($item['path'] ?? '');
                $mime = (string) ($item['mime'] ?? 'application/octet-stream');

                if ($url !== '' && str_starts_with($url, 'https://res.cloudinary.com/')) {
                    $skipped++;

                    continue;
                }

                if ($path === '') {
                    $missing++;

                    $this->warn("  {$snapshot->id()} media[{$i}]: path kosong, dilewati.");

                    continue;
                }

                $local = storage_path('app/public/'.$path);

                if (! is_file($local)) {
                    $missing++;

                    $this->warn("  {$snapshot->id()} media[{$i}]: file lokal tidak ada ({$path}).");

                    continue;
                }

                if ($dryRun) {
                    $this->line("  [dry-run] {$snapshot->id()} → {$path}");

                    $changed = true;

                    continue;
                }

                Storage::disk('cloudinary')->put($path, (string) file_get_contents($local));
                $media[$i]['url'] = $uploader->publicUrl($path, $mime);
                $changed = true;
                $uploaded++;

                $this->info("  diunggah : {$path}");
            }

            if ($changed && ! $dryRun) {
                $db->collection('reports')->document($snapshot->id())->set(['media' => $media], ['merge' => true]);
                $updated++;
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? "Selesai (dry-run). {$uploaded} berkas akan diunggah."
            : "Selesai. {$uploaded} berkas diunggah, {$updated} dokumen diperbarui, {$skipped} sudah di Cloudinary, {$missing} dilewati.");

        return self::SUCCESS;
    }
}
