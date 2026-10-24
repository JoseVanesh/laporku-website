<?php

declare(strict_types=1);

namespace App\Services\Firestore;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;

class ReportStore
{
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
