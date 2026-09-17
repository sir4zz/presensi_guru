@props([
    'variant' => 'neutral',
])

<span class="badge badge-{{ $variant }}" {{ $attributes->merge(['class' => '']) }}>
    {{ $slot }}
</span>
