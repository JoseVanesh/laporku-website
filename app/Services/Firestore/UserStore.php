<?php

declare(strict_types=1);

namespace App\Services\Firestore;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;

class UserStore
{
    public function __construct(private readonly FirestoreClient $db) {}

    public function find(string $uid): ?array
    {
        $snapshot = $this->collection()->document($uid)->snapshot();

        if (! $snapshot->exists()) {
            return null;
        }

        return ['uid' => $snapshot->id()] + $snapshot->data();
    }

    public function findByEmail(string $email): ?array
    {
        $documents = $this->collection()
            ->where('email', '=', $email)
            ->limit(1)
            ->documents();

        foreach ($documents as $snapshot) {
            if ($snapshot->exists()) {
                return ['uid' => $snapshot->id()] + $snapshot->data();
            }
        }

        return null;
    }

    public function create(string $uid, array $data): array
    {
        $data['created_at'] = now()->toIso8601String();
        $this->collection()->document($uid)->set($data);

        return ['uid' => $uid] + $data;
    }

    public function update(string $uid, array $data): void
    {
        $this->collection()->document($uid)->set($data, ['merge' => true]);
    }

    /**
     * @param  array{role?: string}  $filters
     * @return array{items: array<int, array<string, mixed>>, has_more: bool}
     */
    public function paginate(array $filters = [], int $perPage = 20, int $offset = 0): array
    {
        $query = $this->collection();

        if (! empty($filters['role'])) {
            $query = $query->where('role', '=', $filters['role']);
        }

        $documents = $query
            ->orderBy('created_at', Query::DIR_DESCENDING)
            ->limit($perPage + 1)
            ->offset($offset)
            ->documents();

        $items = [];

        foreach ($documents as $snapshot) {
            if ($snapshot->exists()) {
                $items[] = ['uid' => $snapshot->id()] + $snapshot->data();
            }
        }

        $hasMore = count($items) > $perPage;

        if ($hasMore) {
            array_pop($items);
        }

        return ['items' => $items, 'has_more' => $hasMore];
    }

    /**
     * @return array<string, mixed>
     */
    private function collection(): CollectionReference
    {
        return $this->db->collection('users');
    }
}
