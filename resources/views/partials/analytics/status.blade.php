@php
    $status = is_array($byStatus ?? null) ? $byStatus : [];
    $total = (int) ($total ?? array_sum($status));
    $colors = [
        'baru' => '#f59e0b',
        'diproses' => '#0ea5e9',
        'selesai' => '#248a55',
        'ditolak' => '#f43f5e',
    ];
    $segments = [];
    $cursor = 0.0;

    foreach ($colors as $kode => $warna) {
        $jumlah = (int) ($status[$kode] ?? 0);

        if ($total <= 0 || $jumlah <= 0) {
            continue;
        }

        $start = ($cursor / $total) * 100;
        $cursor += $jumlah;
        $end = ($cursor / $total) * 100;
        $segments[] = $warna.' '.$start.'% '.$end.'%';
    }

    $gradient = $segments === [] ? 'conic-gradient(#e2e8f0 0% 100%)' : 'conic-gradient('.implode(', ', $segments).')';
@endphp

<div class="flex flex-col items-center gap-5 sm:flex-row sm:items-center">
    <div class="relative h-36 w-36 shrink-0 rounded-full" style="background: {{ $gradient }}">
        <div class="absolute inset-5 flex flex-col items-center justify-center rounded-full bg-white text-center">
            <span class="text-2xl font-bold text-slate-900">{{ $total }}</span>
            <span class="text-[11px] font-medium text-slate-500">laporan</span>
        </div>
    </div>

    <div class="w-full space-y-2">
        @foreach ($statusLabels as $kode => $label)
            <div class="flex items-center justify-between gap-3 text-sm">
                <span class="flex items-center gap-2 text-slate-600">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $colors[$kode] ?? '#94a3b8' }}"></span>
                    {{ $label }}
                </span>
                <span class="font-semibold text-slate-800">{{ (int) ($status[$kode] ?? 0) }}</span>
            </div>
        @endforeach
    </div>
</div>
