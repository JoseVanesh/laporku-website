<?php

namespace Tests\Feature;

use App\Services\Firestore\UserStore;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SeedAkunCommandTest extends TestCase
{
    public function test_dry_run_does_not_create_accounts_or_csv(): void
    {
        $this->mock(UserStore::class);

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laporku-seed-test-'.uniqid('', true).'.csv';

        $this->artisan('firebase:seed-akun', [
            '--warga' => 20,
            '--output' => $path,
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('Dry run selesai. Tidak ada akun dibuat.')
            ->expectsOutputToContain('Total rencana : 32')
            ->assertSuccessful();

        $this->assertFalse(File::exists($path));
    }
}
