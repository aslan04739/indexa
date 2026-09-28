@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])
<label class="flex flex-col gap-1">
    <span class="text-sm font-semibold">{{ $label }}</span>
    @if ($type === 'textarea')
        <textarea name="{{ $name }}" id="{{ $name }}" rows="6" {{ $attributes->merge(['class' => 'rounded border border-line bg-white px-3 py-2']) }}>{{ old($name, $value) }}</textarea>
    @else
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ $type === 'password' ? '' : old($name, $value) }}" {{ $attributes->merge(['class' => 'rounded border border-line bg-white px-3 py-2']) }}>
    @endif
    @if ($hint)<span class="text-sm text-muted">{{ $hint }}</span>@endif
</label>
