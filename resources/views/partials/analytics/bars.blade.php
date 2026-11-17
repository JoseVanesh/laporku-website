@php
    $rows = is_array($items ?? null) ? $items : [];
    $max = max(1, ...array_map(static fn (array $item): int => (int) ($item['total'] ?? 0), $rows ?: [['total' => 1]]));
    $barColor = $color ?? 'bg-brand-500';
@endphp

@if (empty($rows))
    <p class="text-sm text-slate-500">{{ $empty ?? 'Belum ada data.' }}</p>
@else
    <div class="space-y-3">
        @foreach ($rows as $row)
            @php($value = (int) ($row['total'] ?? 0))
            <div>
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="truncate font-medium text-slate-700">{{ $row['label'] ?? $row['nama'] ?? $row['blok'] ?? '-' }}</span>
                    <span class="shrink-0 text-slate-500">{{ $value }} laporan</span>
                </div>
                <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-2.5 rounded-full {{ $barColor }}" style="width: {{ min(100, ($value / $max) * 100) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
@endif
