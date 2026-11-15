<?php

declare(strict_types=1);

namespace App\Services\Firestore;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;

class AnnouncementStore
{
    public function __construct(private readonly FirestoreClient $db) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): string
    {
        return $this->collection()->add($data)->id();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $snapshot = $this->collection()->document($id)->snapshot();

        if (! $snapshot->exists()) {
            return null;
        }

        return ['id' => $snapshot->id()] + $snapshot->data();
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, has_more: bool}
     */
    public function paginate(int $perPage = 15, int $offset = 0): array
    {
        $documents = $this->collection()
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function latest(int $limit = 3): array
    {
        $documents = $this->collection()
            ->orderBy('created_at', Query::DIR_DESCENDING)
            ->limit($limit)
            ->documents();

        $items = [];

        foreach ($documents as $snapshot) {
            if ($snapshot->exists()) {
                $items[] = ['id' => $snapshot->id()] + $snapshot->data();
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): void
    {
        $this->collection()->document($id)->set($data, ['merge' => true]);
    }

    public function delete(string $id): void
    {
        $this->collection()->document($id)->delete();
    }

    private function collection(): CollectionReference
    {
        return $this->db->collection('announcements');
    }
}
