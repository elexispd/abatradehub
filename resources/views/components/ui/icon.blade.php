@props([
    'name',
    'filled' => false,
])

<span {{ $attributes->class(['material-symbols-outlined', 'is-filled' => $filled]) }} aria-hidden="true">{{ $name }}</span>

