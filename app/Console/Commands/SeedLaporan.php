<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Firestore\CategoryStore;
use App\Services\Firestore\ReportStore;
use App\Services\Firestore\UserStore;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SeedLaporan extends Command
{
    protected $signature = 'firebase:seed-laporan
        {--laporan=260 : Total laporan acak yang dibuat}
        {--aktif=130 : Jumlah akun warga yang diberi laporan}
        {--hari=90 : Rentang hari ke belakang}
        {--batch=v1 : Penanda batch agar idempotent}
        {--dry-run : Tampilkan rencana tanpa menyimpan laporan}';

    protected $description = 'Membuat laporan acak dari akun warga agar grafik analisis naik-turun';

    public function handle(UserStore $users, CategoryStore $categories, ReportStore $reports): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $batch = $this->sanitizeBatch((string) $this->option('batch'));
        $days = max(7, (int) $this->option('hari'));
        $requestedReports = max(0, (int) $this->option('laporan'));
        $requestedActive = max(0, (int) $this->option('aktif'));

        $warga = $this->seedWarga($users);
        $categoryNames = $categories->names();

        if ($warga === []) {
            $this->error('Tidak ditemukan akun warga seed (warga###@gmail.com). Jalankan firebase:seed-akun dulu.');

            return self::FAILURE;
        }

        if ($categoryNames === []) {
            $this->error('Jenis pelaporan belum ada. Jalankan firebase:seed-categories dulu.');

            return self::FAILURE;
        }

        if ($requestedReports === 0 || $requestedActive === 0) {
            $this->info('Tidak ada laporan yang diminta.');

            return self::SUCCESS;
        }

        mt_srand((int) crc32('laporku-laporan-'.$batch.'-'.$days.'-'.$requestedReports.'-'.$requestedActive));
        shuffle($warga);

        $activeCount = min($requestedActive, $requestedReports, count($warga));
        $active = array_slice($warga, 0, $activeCount);
        $inactiveCount = count($warga) - $activeCount;

        $authors = $active;

        while (count($authors) < $requestedReports) {
            $authors[] = $active[mt_rand(0, $activeCount - 1)];
        }

        shuffle($authors);

        $weights = $this->dayWeights($days);
        $center = (array) config('laporku.map.center', ['lat' => -6.2, 'lng' => 106.8166]);
        $plan = [];

        foreach ($authors as $index => $author) {
            $date = $this->randomDate($weights, $days);
            $status = $this->statusFor($date);
            $content = $this->content($categoryNames, $author);
            $id = 'demo-'.$batch.'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            $plan[] = [
                'id' => $id,
                'uid_penulis' => $author['uid'],
                'nama_penulis' => $author['nama'],
                'email_penulis' => $author['email'],
                'blok' => $author['blok'],
                'kategori' => $content['kategori'],
                'status' => $status,
                'created_at' => $date->toIso8601String(),
                'data' => $this->reportData($author, $content, $status, $date, $center, $batch),
            ];
        }

        $this->summary($plan, $activeCount, $inactiveCount, $days, $batch, $dryRun);

        if ($dryRun) {
            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        foreach ($plan as $item) {
            if ($reports->createWithId($item['id'], $item['data'])) {
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info('Dibuat   : '.$created);
        $this->line('Dilewati : '.$skipped.' (dokumen dengan ID batch yang sama sudah ada)');
        $this->line('Batch    : '.$batch);

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{uid: string, nama: string, email: string, blok: string, rumah: string}>
     */
    private function seedWarga(UserStore $users): array
    {
        $items = [];
        $page = 1;

        do {
            $result = $users->paginate(['role' => 'warga'], 100, ($page - 1) * 100);

            foreach ($result['items'] as $user) {
                $email = (string) ($user['email'] ?? '');

                if (preg_match('/^warga[0-9]{3}@gmail\.com$/i', $email) !== 1) {
                    continue;
                }

                $items[] = [
                    'uid' => (string) ($user['uid'] ?? ''),
                    'nama' => (string) ($user['nama'] ?? 'Warga'),
                    'email' => $email,
                    'blok' => (string) ($user['blok'] ?? ''),
                    'rumah' => (string) ($user['rumah'] ?? ''),
                ];
            }

            $page++;
        } while ($result['has_more']);

        usort($items, static fn (array $a, array $b): int => strcmp($a['email'], $b['email']));

        return $items;
    }

    /**
     * @return array<int, int>
     */
    private function dayWeights(int $days): array
    {
        $weights = [];

        for ($offset = 0; $offset < $days; $offset++) {
            $date = now()->subDays($offset);
            $weekend = in_array($date->dayOfWeekIso, [6, 7], true);
            $wave = 10 + (int) round(6 * sin(($offset + 2) / 4.1));
            $noise = mt_rand(0, 9);
            $weight = (int) round(max(2, $wave + $noise) * ($weekend ? 1.2 : 1.0));
            $weights[$offset] = $weight;
        }

        return $weights;
    }

    /**
     * @param  array<int, int>  $weights
     */
    private function randomDate(array $weights, int $days): Carbon
    {
        $total = array_sum($weights);
        $pick = mt_rand(1, max(1, $total));
        $offset = $days - 1;

        foreach ($weights as $candidate => $weight) {
            $pick -= $weight;

            if ($pick <= 0) {
                $offset = $candidate;
                break;
            }
        }

        $date = now()->startOfDay()->subDays($offset)->addHours(mt_rand(6, 21))->addMinutes(mt_rand(0, 59));

        if ($date->isFuture()) {
            $date = now()->subMinutes(mt_rand(5, 180));
        }

        return $date;
    }

    private function statusFor(Carbon $date): string
    {
        $age = (int) $date->diffInDays(now());

        if ($age > 30) {
            return $this->weighted(['selesai' => 72, 'diproses' => 12, 'ditolak' => 10, 'baru' => 6]);
        }

        if ($age > 7) {
            return $this->weighted(['selesai' => 42, 'diproses' => 30, 'ditolak' => 10, 'baru' => 18]);
        }

        return $this->weighted(['baru' => 46, 'diproses' => 34, 'selesai' => 14, 'ditolak' => 6]);
    }

    /**
     * @param  array<string, int>  $weights
     */
    private function weighted(array $weights): string
    {
        $total = array_sum($weights);
        $pick = mt_rand(1, max(1, $total));

        foreach ($weights as $value => $weight) {
            $pick -= $weight;

            if ($pick <= 0) {
                return (string) $value;
            }
        }

        return (string) array_key_first($weights);
    }

    /**
     * @param  array<int, string>  $categories
     * @param  array{blok: string, rumah: string}  $author
     * @return array{kategori: string, judul: string, deskripsi: string, lokasi: string}
     */
    private function content(array $categories, array $author): array
    {
        $category = $categories[mt_rand(0, count($categories) - 1)];
        $blok = $author['blok'] !== '' ? $author['blok'] : '-';
        $rumah = $author['rumah'] !== '' ? $author['rumah'] : '-';

        $templates = [
            'Keamanan' => [
                ['Lampu penerangan di Blok {blok} mati', 'Lampu jalan di sekitar Blok {blok} rumah {rumah} sudah beberapa hari tidak menyala sehingga area terasa gelap pada malam hari. Mohon pengecekan dan perbaikan.'],
                ['Pintu gerbang area Blok {blok} rusak', 'Engsel pintu gerbang dekat rumah {rumah} Blok {blok} longgar dan sulit ditutup rapat. Warga khawatir keamanan lingkungan berkurang.'],
                ['Aktivitas mencurigakan di Blok {blok}', 'Warga melihat orang asing mondar-mandir di sekitar Blok {blok} rumah {rumah} pada malam hari. Mohon patroli tambahan.'],
            ],
            'Kebersihan' => [
                ['Sampah menumpuk di Blok {blok}', 'Tumpukan sampah di dekat rumah {rumah} Blok {blok} belum terangkut dan mulai menimbulkan bau. Mohon penjadwalan pengangkutan.'],
                ['Selokan kotor di Blok {blok}', 'Selokan depan rumah {rumah} Blok {blok} dipenuhi sampah dan daun kering sehingga alirannya terganggu.'],
                ['Area taman Blok {blok} perlu dibersihkan', 'Dedaunan dan sampah berserakan di area taman dekat Blok {blok}. Perlu pembersihan agar lingkungan kembali rapi.'],
            ],
            'Infrastruktur' => [
                ['Jalan berlubang di Blok {blok}', 'Terdapat lubang di jalan dekat rumah {rumah} Blok {blok} yang cukup dalam dan berisiko bagi pengendara. Mohon perbaikan.'],
                ['Saluran air tersumbat di Blok {blok}', 'Saluran air depan rumah {rumah} Blok {blok} tersumbat sehingga air menggenang saat hujan.'],
                ['Trotoar rusak di Blok {blok}', 'Permukaan trotoar di sekitar Blok {blok} rumah {rumah} retak dan tidak rata. Perlu perbaikan untuk keselamatan pejalan kaki.'],
            ],
            'Lingkungan' => [
                ['Genangan air di Blok {blok}', 'Setiap hujan, area dekat rumah {rumah} Blok {blok} tergenang cukup lama. Mohon dicek saluran pembuangannya.'],
                ['Pohon rawan tumbang di Blok {blok}', 'Ada pohon besar dengan dahan rapuh di dekat rumah {rumah} Blok {blok}. Mohon peninjauan agar tidak membahayakan warga.'],
                ['Tanaman liar menutupi jalan Blok {blok}', 'Semak dan tanaman liar tumbuh lebat di sisi jalan Blok {blok} dekat rumah {rumah} sehingga mengganggu pandangan.'],
            ],
            'Lainnya' => [
                ['Fasilitas umum Blok {blok} perlu perhatian', 'Warga melaporkan kondisi fasilitas umum di sekitar Blok {blok} rumah {rumah} yang perlu dicek dan ditindaklanjuti.'],
                ['Gangguan kebisingan di Blok {blok}', 'Terjadi kebisingan berulang di sekitar Blok {blok} rumah {rumah} pada jam istirahat. Mohon penanganan.'],
                ['Permintaan pengecekan area Blok {blok}', 'Warga Blok {blok} rumah {rumah} meminta pengecekan kondisi lingkungan karena ada potensi gangguan kenyamanan bersama.'],
            ],
        ];

        $list = $templates[$category] ?? $templates['Lainnya'];
        $item = $list[mt_rand(0, count($list) - 1)];
        $replace = ['{blok}' => $blok, '{rumah}' => $rumah];

        return [
            'kategori' => $category,
            'judul' => strtr($item[0], $replace),
            'deskripsi' => strtr($item[1], $replace),
            'lokasi' => $author['blok'] !== '' ? 'Blok '.$blok.($author['rumah'] !== '' ? ', Rumah '.$rumah : '') : 'Lingkungan perumahan',
        ];
    }

    /**
     * @param  array{uid: string, nama: string, email: string, blok: string, rumah: string}  $author
     * @param  array{kategori: string, judul: string, deskripsi: string, lokasi: string}  $content
     * @param  array{lat: float, lng: float}  $center
     * @return array<string, mixed>
     */
    private function reportData(array $author, array $content, string $status, Carbon $date, array $center, string $batch): array
    {
        $hasLocation = mt_rand(1, 100) <= 75;
        $centerLat = (float) ($center['lat'] ?? -6.2);
        $centerLng = (float) ($center['lng'] ?? 106.8166);

        $data = [
            'uid_penulis' => $author['uid'],
            'nama_penulis' => $author['nama'],
            'email_penulis' => $author['email'],
            'judul' => $content['judul'],
            'kategori' => $content['kategori'],
            'deskripsi' => $content['deskripsi'],
            'lokasi' => $content['lokasi'],
            'lat' => $hasLocation ? round($centerLat + (mt_rand(-120, 120) / 10000), 6) : null,
            'lng' => $hasLocation ? round($centerLng + (mt_rand(-180, 180) / 10000), 6) : null,
            'blok' => $author['blok'],
            'rumah' => $author['rumah'],
            'status' => $status,
            'media' => [],
            'jmedia' => 0,
            'seed_batch' => $batch,
            'created_at' => $date->toIso8601String(),
            'updated_at' => $date->toIso8601String(),
        ];

        if ($status !== 'baru') {
            $data['updated_at'] = $date->copy()->addHours(mt_rand(2, 72))->min(now())->toIso8601String();
        }

        return $data;
    }

    /**
     * @param  array<int, array<string, mixed>>  $plan
     */
    private function summary(array $plan, int $activeCount, int $inactiveCount, int $days, string $batch, bool $dryRun): void
    {
        $this->newLine();
        $this->info($dryRun ? 'Dry run selesai. Tidak ada laporan disimpan.' : 'Menyiapkan laporan acak...');
        $this->line('Batch         : '.$batch);
        $this->line('Total laporan : '.count($plan));
        $this->line('Warga aktif   : '.$activeCount);
        $this->line('Warga pasif   : '.$inactiveCount);
        $this->line('Rentang hari  : '.$days);

        $samples = array_slice($plan, 0, 5);

        if ($samples !== []) {
            $this->newLine();
            $this->line('Contoh laporan:');
            $this->table(
                ['ID', 'Email', 'Kategori', 'Blok', 'Status', 'Tanggal'],
                array_map(static fn (array $item): array => [
                    $item['id'],
                    $item['email_penulis'],
                    $item['kategori'],
                    $item['blok'],
                    $item['status'],
                    $item['created_at'],
                ], $samples),
            );
        }
    }

    private function sanitizeBatch(string $batch): string
    {
        $batch = trim($batch);
        $batch = (string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $batch);
        $batch = trim($batch, '-');

        if ($batch === '') {
            return 'v1';
        }

        return substr($batch, 0, 24);
    }
}
