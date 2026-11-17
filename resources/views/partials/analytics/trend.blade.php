@php
    $points = is_array($daily ?? null) ? $daily : [];
    $count = count($points);
    $width = 760;
    $height = 220;
    $padX = 28;
    $padY = 24;
    $max = max(1, ...array_map(static fn (array $item): int => (int) ($item['total'] ?? 0), $points ?: [['total' => 1]]));
    $innerWidth = $width - ($padX * 2);
    $innerHeight = $height - ($padY * 2);
    $step = $count > 1 ? $innerWidth / ($count - 1) : 0;

    $coords = [];

    foreach ($points as $index => $item) {
        $x = $padX + ($step * $index);
        $y = $height - $padY - (((int) ($item['total'] ?? 0)) / $max) * $innerHeight;
        $coords[] = ['x' => round($x, 2), 'y' => round($y, 2), 'item' => $item];
    }

    $line = implode(' ', array_map(static fn (array $point): string => $point['x'].','.$point['y'], $coords));
    $area = $coords === []
        ? ''
        : 'M'.$coords[0]['x'].','.($height - $padY).' L'.implode(' L', array_map(static fn (array $point): string => $point['x'].','.$point['y'], $coords)).' L'.end($coords)['x'].','.($height - $padY).' Z';
    $labelEvery = max(1, (int) ceil($count / 7));
@endphp

<div class="w-full overflow-hidden">
    <svg viewBox="0 0 {{ $width }} {{ $height }}" class="h-56 w-full" role="img" aria-label="Tren jumlah laporan">
        <defs>
            <linearGradient id="trend-fill" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#34ab6b" stop-opacity="0.28" />
                <stop offset="100%" stop-color="#34ab6b" stop-opacity="0.02" />
            </linearGradient>
        </defs>

        @for ($i = 0; $i <= 4; $i++)
            @php($gridY = $padY + ($innerHeight / 4) * $i)
            <line x1="{{ $padX }}" y1="{{ $gridY }}" x2="{{ $width - $padX }}" y2="{{ $gridY }}" stroke="#e2efe7" stroke-width="1" />
        @endfor

        @if ($area !== '')
            <path d="{{ $area }}" fill="url(#trend-fill)" />
        @endif

        @if ($line !== '')
            <polyline points="{{ $line }}" fill="none" stroke="#248a55" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
        @endif

        @foreach ($coords as $index => $point)
            @if ($count <= 45 || $index % $labelEvery === 0 || $index === $count - 1)
                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="2.5" fill="#fff" stroke="#248a55" stroke-width="2" />
            @endif
            @if ($index % $labelEvery === 0 || $index === $count - 1)
                <text x="{{ $point['x'] }}" y="{{ $height - 6 }}" text-anchor="middle" font-size="10" fill="#94a3b8">{{ $point['item']['label'] ?? '' }}</text>
            @endif
        @endforeach
    </svg>
</div>
