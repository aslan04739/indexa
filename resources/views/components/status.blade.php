@props(['status'])
@php
    $tone = match ($status) {
        'completed', 'approved', 'paid', 'ok' => 'bg-brand/10 text-brand',
        'refused', 'cancelled', 'rejected', 'failed', 'missing', 'noindex', 'unreachable', 'disputed' => 'bg-red-50 text-red-800',
        default => 'bg-amber-50 text-amber-800',
    };
@endphp
<span class="inline-block rounded px-2 py-0.5 text-xs font-semibold {{ $tone }}">{{ __('status.'.$status) }}</span>
