<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Firestore\UserStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Request\CreateUser;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class SeedAkun extends Command
{
    protected $signature = 'firebase:seed-akun
        {--warga=200 : Jumlah akun warga yang dibuat}
        {--output= : Path CSV kredensial (default di folder temp)}
        {--dry-run : Tampilkan rencana tanpa membuat akun}';

    protected $description = 'Membuat akun RT, RW, dan warga di Firebase Auth beserta dokumen Firestore';

    /**
     * @var array<int, array{email: string, password: string, role: string, blok: string, nama: string, status: string}>
     */
    private array $rows = [];

    private int $created = 0;

    private int $skipped = 0;

    private int $failed = 0;

    public function handle(UserStore $users): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $wargaCount = max(0, (int) $this->option('warga'));
        $output = $this->resolveOutputPath($this->option('output'));

        $accounts = array_merge($this->staffAccounts(), $this->wargaAccounts($wargaCount));

        if ($dryRun) {
            foreach ($accounts as $account) {
                $this->rows[] = [...$account, 'password' => '', 'status' => 'rencana'];
            }

            $this->summary($accounts, $output, $dryRun);

            return self::SUCCESS;
        }

        $auth = Firebase::auth();

        foreach ($accounts as $account) {
            try {
                $this->provision($auth, $users, $account);
            } catch (Throwable $e) {
                $this->failed++;
                $this->rows[] = [
                    'email' => $account['email'],
                    'password' => '',
                    'role' => $account['role'],
                    'blok' => $account['blok'],
                    'nama' => $account['nama'],
                    'status' => 'gagal: '.$e->getMessage(),
                ];
                $this->warn('  gagal   : '.$account['email'].' - '.$e->getMessage());
            }
        }

        $this->writeCsv($output);
        $this->summary($accounts, $output, $dryRun);

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array{email: string, password: string, role: string, blok: string, bloks: array<int, string>, nama: string, rumah: string}  $account
     */
    private function provision(Auth $auth, UserStore $users, array $account): void
    {
        $existing = null;

        try {
            $existing = $auth->getUserByEmail($account['email']);
        } catch (UserNotFound) {
            // Belum ada, akan dibuat di bawah.
        }

        if ($existing === null) {
            $record = $auth->createUser(
                CreateUser::new()
                    ->withUnverifiedEmail($account['email'])
                    ->withClearTextPassword($account['password'])
                    ->withDisplayName($account['nama']),
            );

            $uid = $record->uid;
            $status = 'dibuat';
            $this->created++;
            $this->info('  dibuat   : '.$account['email']);
        } else {
            $uid = $existing->uid;
            $status = 'sudah ada';
            $this->skipped++;
            $this->line('  dilewati : '.$account['email']);
        }

        $document = $users->find($uid) ?? $users->findByEmail($account['email']);
        $targetUid = (string) ($document['uid'] ?? $uid);

        $data = [
            'email' => $account['email'],
            'role' => $account['role'],
            'blok' => $account['blok'],
            'bloks' => $account['bloks'],
            'rumah' => $account['rumah'],
        ];

        if (trim((string) ($document['nama'] ?? '')) === '') {
            $data['nama'] = $account['nama'];
        }

        if ($document === null) {
            $data['nama'] = $account['nama'];
            $data['telepon'] = '';

            $users->create($targetUid, $data);
        } else {
            if (! array_key_exists('telepon', $document)) {
                $data['telepon'] = '';
            }

            $users->update($targetUid, $data);
        }

        $this->rows[] = [
            'email' => $account['email'],
            'password' => $status === 'dibuat' ? $account['password'] : '',
            'role' => $account['role'],
            'blok' => $account['blok'],
            'nama' => $account['nama'],
            'status' => $status,
        ];
    }

    /**
     * @return array<int, array{email: string, password: string, role: string, blok: string, bloks: array<int, string>, nama: string, rumah: string}>
     */
    private function staffAccounts(): array
    {
        $accounts = [];

        foreach ($this->blocks() as $index => $block) {
            $accounts[] = $this->account(
                email: 'pkprt'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'@gmail.com',
                role: 'rt',
                blok: $block,
                bloks: [],
                nama: 'RT Blok '.$block,
            );
        }

        $accounts[] = $this->account(
            email: 'josevanesh@gmail.com',
            role: 'rw',
            blok: 'C',
            bloks: ['A', 'B', 'C', 'D', 'E'],
            nama: 'RW 01',
        );

        $accounts[] = $this->account(
            email: 'pkprw02@gmail.com',
            role: 'rw',
            blok: 'F',
            bloks: ['F', 'G', 'H', 'I', 'J'],
            nama: 'RW 02',
        );

        return $accounts;
    }

    /**
     * @return array<int, array{email: string, password: string, role: string, blok: string, bloks: array<int, string>, nama: string, rumah: string}>
     */
    private function wargaAccounts(int $count): array
    {
        if ($count === 0) {
            return [];
        }

        $blocks = $this->blocks();
        $perBlock = intdiv($count, count($blocks));
        $remainder = $count % count($blocks);

        $slots = [];

        foreach ($blocks as $index => $block) {
            $total = $perBlock + ($index < $remainder ? 1 : 0);

            for ($rumah = 1; $rumah <= $total; $rumah++) {
                $slots[] = ['blok' => $block, 'rumah' => str_pad((string) $rumah, 2, '0', STR_PAD_LEFT)];
            }
        }

        mt_srand((int) crc32('laporku-warga-v1'));
        shuffle($slots);

        $accounts = [];

        foreach ($slots as $index => $slot) {
            $nomor = $index + 1;

            $accounts[] = [
                'email' => 'warga'.str_pad((string) $nomor, 3, '0', STR_PAD_LEFT).'@gmail.com',
                'password' => Str::random(16),
                'role' => 'warga',
                'blok' => $slot['blok'],
                'bloks' => [],
                'nama' => 'Warga '.str_pad((string) $nomor, 3, '0', STR_PAD_LEFT),
                'rumah' => $slot['rumah'],
            ];
        }

        return $accounts;
    }

    /**
     * @param  array{email: string, role: string, blok: string, bloks: array<int, string>, nama: string}  $account
     * @return array{email: string, password: string, role: string, blok: string, bloks: array<int, string>, nama: string, rumah: string}
     */
    private function account(string $email, string $role, string $blok, array $bloks, string $nama): array
    {
        return [
            'email' => $email,
            'password' => Str::random(16),
            'role' => $role,
            'blok' => $blok,
            'bloks' => $bloks,
            'nama' => $nama,
            'rumah' => '',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function blocks(): array
    {
        return array_values((array) config('laporku.blocks', range('A', 'J')));
    }

    /**
     * @param  array<int, array<string, string>>  $accounts
     */
    private function summary(array $accounts, string $output, bool $dryRun): void
    {
        $this->newLine();
        $this->info($dryRun ? 'Dry run selesai. Tidak ada akun dibuat.' : 'Selesai.');
        $this->line('Total rencana : '.count($accounts));
        $this->line('Dibuat        : '.$this->created);
        $this->line('Sudah ada     : '.$this->skipped);
        $this->line('Gagal         : '.$this->failed);
        $this->line(($dryRun ? 'Lokasi CSV    : ' : 'CSV kredensial: ').$output);

        $samples = array_slice(array_values(array_filter(
            $this->rows,
            static fn (array $row): bool => $row['status'] !== 'sudah ada',
        )), 0, 5);

        if ($samples === []) {
            $samples = array_slice($this->rows, 0, 5);
        }

        if ($samples !== []) {
            $this->newLine();
            $this->line('Contoh akun:');
            $this->table(
                ['Email', 'Password', 'Role', 'Blok', 'Nama', 'Status'],
                array_map(static fn (array $row): array => [
                    $row['email'],
                    $row['password'] !== '' ? $row['password'] : '-',
                    $row['role'],
                    $row['blok'],
                    $row['nama'],
                    $row['status'],
                ], $samples),
            );
        }
    }

    private function resolveOutputPath(?string $output): string
    {
        if (is_string($output) && trim($output) !== '') {
            $output = trim($output);

            if (preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $output) === 1) {
                return $output;
            }

            return base_path($output);
        }

        return rtrim(sys_get_temp_dir(), '\\/')
            .DIRECTORY_SEPARATOR.'opencode'
            .DIRECTORY_SEPARATOR.'laporku-akun-'.now()->format('Ymd-His').'.csv';
    }

    private function writeCsv(string $path): void
    {
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'w');

        if ($handle === false) {
            throw new \RuntimeException('Tidak dapat membuka file CSV: '.$path);
        }

        fputcsv($handle, ['email', 'password', 'role', 'blok', 'nama', 'status']);

        foreach ($this->rows as $row) {
            fputcsv($handle, [
                $row['email'],
                $row['password'],
                $row['role'],
                $row['blok'],
                $row['nama'],
                $row['status'],
            ]);
        }

        fclose($handle);
    }
}
