@props(['variant' => 'primary'])
<button {{ $attributes->merge(['class' => match ($variant) {
    'primary' => 'rounded bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-dark',
    'danger' => 'rounded border border-red-700 px-4 py-2 font-semibold text-red-700 hover:bg-red-50',
    default => 'rounded border border-ink px-4 py-2 font-semibold hover:border-brand hover:text-brand',
}]) }}>{{ $slot }}</button>
