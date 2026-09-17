@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
    'href' => null,
])

@if($href)
    <a href="{{ $href }}" class="btn btn-{{ $variant }} btn-{{ $size }}" {{ $attributes->merge(['class' => '']) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="btn btn-{{ $variant }} btn-{{ $size }}" @if($disabled) disabled @endif {{ $attributes->merge(['class' => '']) }}>
        {{ $slot }}
    </button>
@endif
