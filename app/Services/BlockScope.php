<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Firestore\ReportStore;

class BlockScope
{
    public function role(): string
    {
        return (string) session('firebase.role', 'warga');
    }

    public function isStaff(): bool
    {
        return in_array($this->role(), ['rw', 'rt', 'super_admin'], true);
    }

    public function isManager(): bool
    {
        return in_array($this->role(), ['rw', 'super_admin'], true);
    }

    /**
     * Blok yang boleh diakses pengguna saat ini. Super admin mengembalikan
     * daftar kosong karena tidak dibatasi blok.
     *
     * @return array<int, string>
     */
    public function managedBlocks(): array
    {
        $role = $this->role();

        if ($role === 'rw') {
            $blocks = array_values(array_filter(array_map(
                static fn (mixed $block): string => trim((string) $block),
                (array) session('firebase.blocks', []),
            )));

            if ($blocks !== []) {
                return array_values(array_unique($blocks));
            }
        }

        if (in_array($role, ['rw', 'rt'], true)) {
            $block = trim((string) session('firebase.blok', ''));

            return $block !== '' ? [$block] : [];
        }

        return [];
    }

    /**
     * Filter yang siap dipakai ReportStore.
     *
     * @return array<string, string|array<int, string>>
     */
    public function reportFilters(): array
    {
        $role = $this->role();

        if (! in_array($role, ['rt', 'rw'], true)) {
            return [];
        }

        $blocks = $this->managedBlocks();

        if ($blocks === []) {
            return ['blok' => ReportStore::EMPTY_SCOPE];
        }

        return ['blok' => $role === 'rt' ? $blocks[0] : $blocks];
    }

    public function canAccessBlock(?string $block): bool
    {
        $role = $this->role();

        if ($role === 'super_admin') {
            return true;
        }

        if (! in_array($role, ['rt', 'rw'], true)) {
            return false;
        }

        return in_array(trim((string) $block), $this->managedBlocks(), true);
    }

    public function label(): string
    {
        $role = $this->role();
        $blocks = $this->managedBlocks();

        if ($role === 'super_admin') {
            return 'Semua blok';
        }

        if ($blocks === []) {
            return 'Blok belum diatur';
        }

        if (count($blocks) === 1) {
            return 'Blok '.$blocks[0];
        }

        return 'Blok '.$blocks[0].'–'.$blocks[array_key_last($blocks)];
    }
}
