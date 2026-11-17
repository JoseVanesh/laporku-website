@php
    $days = is_array($byHari ?? null) ? $byHari : [];
    $max = max(1, ...array_values(array_map(static fn ($value): int => (int) $value, $days ?: [1])));
@endphp

@if (empty($days))
    <p class="text-sm text-slate-500">Belum ada data hari.</p>
@else
    <div class="flex h-36 items-end gap-2">
        @foreach ($days as $label => $value)
            @php($height = ((int) $value) > 0 ? max(8, (((int) $value) / $max) * 100) : 4)
            <div class="flex flex-1 flex-col items-center gap-1.5">
                <span class="text-[10px] font-semibold text-slate-500">{{ (int) $value }}</span>
                <div class="flex h-24 w-full items-end">
                    <div class="w-full rounded-t-lg bg-brand-500/80" style="height: {{ $height }}%"></div>
                </div>
                <span class="text-[10px] font-medium text-slate-500">{{ mb_substr((string) $label, 0, 3) }}</span>
            </div>
        @endforeach
    </div>
@endif
