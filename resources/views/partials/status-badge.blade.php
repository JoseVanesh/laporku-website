@php
    $styles = [
        'baru' => 'bg-amber-100 text-amber-800 ring-amber-200',
        'diproses' => 'bg-sky-100 text-sky-800 ring-sky-200',
        'selesai' => 'bg-brand-100 text-brand-800 ring-brand-200',
        'ditolak' => 'bg-rose-100 text-rose-800 ring-rose-200',
    ];
    $label = $labels[$status] ?? ucfirst((string) $status);
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $styles[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">
    {{ $label }}
</span>
