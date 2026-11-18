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
     * @param  array{role?: string, search?: string}  $filters
     * @return array{items: array<int, array<string, mixed>>, has_more: bool}
     */
    public function paginate(array $filters = [], int $perPage = 20, int $offset = 0): array
    {
        $query = $this->collection();

        if (! empty($filters['role'])) {
            $query = $query->where('role', '=', $filters['role']);
        }

        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $documents = $query
                ->orderBy('created_at', Query::DIR_DESCENDING)
                ->limit(500)
                ->documents();

            $needle = mb_strtolower($search);
            $matches = [];

            foreach ($documents as $snapshot) {
                if (! $snapshot->exists()) {
                    continue;
                }

                $item = ['uid' => $snapshot->id()] + $snapshot->data();

                if ($this->matchesSearch($item, $needle)) {
                    $matches[] = $item;
                }
            }

            $items = array_slice($matches, $offset, $perPage + 1);
            $hasMore = count($items) > $perPage;

            if ($hasMore) {
                array_pop($items);
            }

            return ['items' => $items, 'has_more' => $hasMore];
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
     * @param  array<string, mixed>  $user
     */
    private function matchesSearch(array $user, string $needle): bool
    {
        $blocks = array_filter(array_map(
            static fn (mixed $block): string => trim((string) $block),
            (array) ($user['bloks'] ?? []),
        ));

        $haystacks = [
            (string) ($user['nama'] ?? ''),
            (string) ($user['email'] ?? ''),
            (string) ($user['telepon'] ?? ''),
            (string) ($user['blok'] ?? ''),
            (string) ($user['rumah'] ?? ''),
            (string) ($user['uid'] ?? ''),
            implode(' ', $blocks),
        ];

        foreach ($haystacks as $value) {
            if ($value !== '' && str_contains(mb_strtolower($value), $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ringkasan jumlah pengguna per peran dan daftar blok aktif.
     *
     * @return array{total: int, by_role: array<string, int>, blocks: array<int, string>}
     */
    public function summary(int $limit = 500): array
    {
        $documents = $this->collection()
            ->orderBy('created_at', Query::DIR_DESCENDING)
            ->limit($limit)
            ->documents();

        $total = 0;
        $byRole = [];
        $blocks = [];

        foreach ($documents as $snapshot) {
            if (! $snapshot->exists()) {
                continue;
            }

            $data = $snapshot->data();
            $total++;

            $role = (string) ($data['role'] ?? 'warga');
            $byRole[$role] = ($byRole[$role] ?? 0) + 1;

            $blok = trim((string) ($data['blok'] ?? ''));

            if ($blok !== '') {
                $blocks[$blok] = true;
            }
        }

        $blockList = array_keys($blocks);
        sort($blockList, SORT_NATURAL | SORT_FLAG_CASE);

        return ['total' => $total, 'by_role' => $byRole, 'blocks' => $blockList];
    }

    /**
     * @return array<string, mixed>
     */
    private function collection(): CollectionReference
    {
        return $this->db->collection('users');
    }
}
