<?php

declare(strict_types=1);

namespace App\Services\Firestore;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;
use Illuminate\Support\Carbon;

class ReportStore
{
    /**
     * Filter mustahil untuk RT yang belum memiliki blok, agar tidak
     * mengambil seluruh laporan lingkungan.
     */
    public const EMPTY_SCOPE = '__none__';

    public function __construct(private readonly FirestoreClient $db) {}

    public function create(array $data): string
    {
        return $this->collection()->add($data)->id();
    }

    public function find(string $id): ?array
    {
        $snapshot = $this->collection()->document($id)->snapshot();

        if (! $snapshot->exists()) {
            return null;
        }

        return ['id' => $snapshot->id()] + $snapshot->data();
    }

    /**
     * Filter yang didukung: uid_penulis, status, kategori, blok.
     *
     * @param  array<string, string|null>  $filters
     * @return array{items: array<int, array<string, mixed>>, has_more: bool}
     */
    public function paginate(array $filters = [], int $perPage = 15, int $offset = 0): array
    {
        $query = $this->collection();

        foreach (['uid_penulis', 'status', 'kategori', 'blok'] as $field) {
            if (! empty($filters[$field])) {
                $query = $query->where($field, '=', $filters[$field]);
            }
        }

        $documents = $query
            ->orderBy('created_at', Query::DIR_DESCENDING)
            ->limit($perPage + 1)
            ->offset($offset)
            ->documents();

        $items = [];

        foreach ($documents as $snapshot) {
            if ($snapshot->exists()) {
                $items[] = ['id' => $snapshot->id()] + $snapshot->data();
            }
        }

        $hasMore = count($items) > $perPage;

        if ($hasMore) {
            array_pop($items);
        }

        return ['items' => $items, 'has_more' => $hasMore];
    }

    public function update(string $id, array $data): void
    {
        $this->collection()->document($id)->set($data, ['merge' => true]);
    }

    /**
     * Titik laporan yang memiliki koordinat, untuk ditampilkan di peta.
     *
     * @param  array<string, string|null>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function mapPoints(array $filters = [], int $limit = 300): array
    {
        $query = $this->collection();

        foreach (['status', 'kategori', 'blok'] as $field) {
            if (! empty($filters[$field])) {
                $query = $query->where($field, '=', $filters[$field]);
            }
        }

        $documents = $query
            ->orderBy('created_at', Query::DIR_DESCENDING)
            ->limit($limit)
            ->documents();

        $items = [];

        foreach ($documents as $snapshot) {
            if (! $snapshot->exists()) {
                continue;
            }

            $data = $snapshot->data();

            if (! isset($data['lat'], $data['lng']) || ! is_numeric($data['lat']) || ! is_numeric($data['lng'])) {
                continue;
            }

            $items[] = ['id' => $snapshot->id()] + $data;
        }

        return $items;
    }

    /**
     * Ringkasan jumlah laporan per status untuk kartu statistik dashboard.
     *
     * @param  array<string, string|null>  $filters
     * @return array{total: int, by_status: array<string, int>, with_location: int}
     */
    public function summary(array $filters = [], int $limit = 500): array
    {
        $query = $this->collection();

        foreach (['uid_penulis', 'status', 'kategori', 'blok'] as $field) {
            if (! empty($filters[$field])) {
                $query = $query->where($field, '=', $filters[$field]);
            }
        }

        $documents = $query
            ->orderBy('created_at', Query::DIR_DESCENDING)
            ->limit($limit)
            ->documents();

        $total = 0;
        $withLocation = 0;
        $byStatus = ['baru' => 0, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0];

        foreach ($documents as $snapshot) {
            if (! $snapshot->exists()) {
                continue;
            }

            $data = $snapshot->data();
            $total++;

            $status = (string) ($data['status'] ?? 'baru');
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            if (isset($data['lat'], $data['lng']) && is_numeric($data['lat']) && is_numeric($data['lng'])) {
                $withLocation++;
            }
        }

        return ['total' => $total, 'by_status' => $byStatus, 'with_location' => $withLocation];
    }

    /**
     * Jumlah laporan per blok untuk grafik pantauan RW dan RT.
     *
     * @param  array<string, string|null>  $filters
     * @return array{bloks: array<int, array{blok: string, total: int}>, total: int}
     */
    public function blokSummary(array $filters = [], int $limit = 500): array
    {
        $query = $this->collection();

        foreach (['uid_penulis', 'status', 'kategori', 'blok'] as $field) {
            if (! empty($filters[$field])) {
                $query = $query->where($field, '=', $filters[$field]);
            }
        }

        $documents = $query
            ->orderBy('created_at', Query::DIR_DESCENDING)
            ->limit($limit)
            ->documents();

        $totals = [];

        foreach ($documents as $snapshot) {
            if (! $snapshot->exists()) {
                continue;
            }

            $data = $snapshot->data();
            $blok = (string) ($data['blok'] ?? '');

            if ($blok === '') {
                continue;
            }

            $totals[$blok] = ($totals[$blok] ?? 0) + 1;
        }

        $bloks = [];

        foreach ($totals as $blok => $total) {
            $bloks[] = ['blok' => (string) $blok, 'total' => $total];
        }

        usort($bloks, static fn (array $a, array $b): int => $b['total'] <=> $a['total']);

        return ['bloks' => $bloks, 'total' => array_sum($totals)];
    }

    /**
     * Agregasi analitik laporan dalam rentang hari tertentu.
     *
     * @param  array<string, string|null>  $filters
     * @return array{
     *     total: int,
     *     by_status: array<string, int>,
     *     by_kategori: array<int, array{nama: string, total: int}>,
     *     by_blok: array<int, array{blok: string, total: int, baru: int, diproses: int, selesai: int, ditolak: int}>,
     *     daily: array<int, array{tanggal: string, label: string, total: int}>,
     *     by_hari: array<string, int>,
     *     by_jam: array<int, int>,
     *     peak_hour: int|null,
     *     completion_rate: float,
     *     avg_per_day: float,
     *     active_days: int
     * }
     */
    public function analytics(array $filters = [], int $days = 30, int $limit = 1000): array
    {
        $days = max(1, min(365, $days));
        $start = now()->startOfDay()->subDays($days - 1);

        $query = $this->collection();

        foreach (['uid_penulis', 'status', 'kategori', 'blok'] as $field) {
            if (! empty($filters[$field])) {
                $query = $query->where($field, '=', $filters[$field]);
            }
        }

        $documents = $query
            ->orderBy('created_at', Query::DIR_DESCENDING)
            ->limit($limit)
            ->documents();

        $daily = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $daily[$date->format('Y-m-d')] = [
                'tanggal' => $date->format('Y-m-d'),
                'label' => $date->locale('id')->translatedFormat('d M'),
                'total' => 0,
            ];
        }

        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $byHari = array_fill_keys($dayNames, 0);
        $byJam = array_fill(0, 24, 0);
        $byStatus = ['baru' => 0, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0];
        $byKategori = [];
        $byBlok = [];
        $total = 0;

        foreach ($documents as $snapshot) {
            if (! $snapshot->exists()) {
                continue;
            }

            $data = $snapshot->data();
            $rawDate = $data['created_at'] ?? null;

            if (! is_string($rawDate) || $rawDate === '') {
                continue;
            }

            try {
                $created = Carbon::parse($rawDate)->timezone(config('app.timezone', 'Asia/Jakarta'));
            } catch (\Throwable) {
                continue;
            }

            if ($created->lt($start)) {
                continue;
            }

            $total++;
            $status = (string) ($data['status'] ?? 'baru');
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;

            $dateKey = $created->format('Y-m-d');

            if (isset($daily[$dateKey])) {
                $daily[$dateKey]['total']++;
            }

            $dayName = $dayNames[(int) $created->dayOfWeekIso - 1];
            $byHari[$dayName] = ($byHari[$dayName] ?? 0) + 1;
            $byJam[(int) $created->format('G')]++;

            $kategori = trim((string) ($data['kategori'] ?? ''));

            if ($kategori !== '') {
                $byKategori[$kategori] = ($byKategori[$kategori] ?? 0) + 1;
            }

            $blok = trim((string) ($data['blok'] ?? ''));

            if ($blok !== '') {
                $byBlok[$blok] ??= ['blok' => $blok, 'total' => 0, 'baru' => 0, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0];
                $byBlok[$blok]['total']++;
                $byBlok[$blok][$status] = ($byBlok[$blok][$status] ?? 0) + 1;
            }
        }

        $kategoriList = [];

        foreach ($byKategori as $nama => $jumlah) {
            $kategoriList[] = ['nama' => (string) $nama, 'total' => $jumlah];
        }

        usort($kategoriList, static fn (array $a, array $b): int => $b['total'] <=> $a['total']);

        $blokList = array_values($byBlok);
        usort($blokList, static fn (array $a, array $b): int => $b['total'] <=> $a['total']);

        $peakHour = null;
        $peakTotal = 0;

        foreach ($byJam as $jam => $jumlah) {
            if ($jumlah > $peakTotal) {
                $peakTotal = $jumlah;
                $peakHour = $jam;
            }
        }

        $dailyValues = array_values($daily);
        $activeDays = count(array_filter($dailyValues, static fn (array $item): bool => $item['total'] > 0));
        $completionRate = $total > 0 ? round((($byStatus['selesai'] ?? 0) / $total) * 100, 1) : 0.0;

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'by_kategori' => $kategoriList,
            'by_blok' => $blokList,
            'daily' => $dailyValues,
            'by_hari' => $byHari,
            'by_jam' => $byJam,
            'peak_hour' => $peakHour,
            'completion_rate' => $completionRate,
            'avg_per_day' => $total > 0 ? round($total / $days, 2) : 0.0,
            'active_days' => $activeDays,
        ];
    }

    /**
     * @param  array<string, mixed>  $comment
     */
    public function addComment(string $reportId, array $comment): void
    {
        $this->collection()->document($reportId)->collection('comments')->add($comment);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function comments(string $reportId): array
    {
        $documents = $this->collection()
            ->document($reportId)
            ->collection('comments')
            ->orderBy('created_at', Query::DIR_ASCENDING)
            ->documents();

        $items = [];

        foreach ($documents as $snapshot) {
            if ($snapshot->exists()) {
                $items[] = ['id' => $snapshot->id()] + $snapshot->data();
            }
        }

        return $items;
    }

    private function collection(): CollectionReference
    {
        return $this->db->collection('reports');
    }
}
