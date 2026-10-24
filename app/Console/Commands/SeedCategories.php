<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Firestore\CategoryStore;
use Illuminate\Console\Command;

class SeedCategories extends Command
{
    protected $signature = 'firebase:seed-categories';

    protected $description = 'Membuat jenis pelaporan default di Firestore jika belum ada';

    public function handle(CategoryStore $categories): int
    {
        $defaults = ['Keamanan', 'Kebersihan', 'Infrastruktur', 'Lingkungan', 'Lainnya'];

        $existing = array_map(mb_strtolower(...), $categories->names());
        $created = 0;

        foreach ($defaults as $nama) {
            if (in_array(mb_strtolower($nama), $existing, true)) {
                $this->line("  dilewati : {$nama}");

                continue;
            }

            $categories->create($nama);
            $created++;
            $this->info("  dibuat   : {$nama}");
        }

        $this->newLine();
        $this->info("Selesai. {$created} jenis pelaporan ditambahkan.");

        return self::SUCCESS;
    }
}
