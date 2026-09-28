@props(['name', 'label', 'options', 'value' => null, 'placeholder' => null])
<label class="flex flex-col gap-1">
    <span class="text-sm font-semibold">{{ $label }}</span>
    <select name="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => 'rounded border border-line bg-white px-3 py-2']) }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $key => $optionLabel)
            <option value="{{ $key }}" @selected((string) old($name, $value) === (string) $key)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</label>
