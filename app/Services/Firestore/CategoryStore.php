<?php

declare(strict_types=1);

namespace App\Services\Firestore;

use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;

class CategoryStore
{
    public function __construct(private readonly FirestoreClient $db) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $documents = $this->collection()
            ->orderBy('nama', Query::DIR_ASCENDING)
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
     * @return array<int, string>
     */
    public function names(): array
    {
        return array_map(fn (array $category) => (string) ($category['nama'] ?? ''), $this->all());
    }

    public function create(string $nama): string
    {
        return $this->collection()->add([
            'nama' => $nama,
            'created_at' => now()->toIso8601String(),
        ])->id();
    }

    public function delete(string $id): void
    {
        $this->collection()->document($id)->delete();
    }

    private function collection(): CollectionReference
    {
        return $this->db->collection('categories');
    }
}
