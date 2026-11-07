@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" class="py-3">
    <ol class="flex flex-wrap items-center gap-1.5 text-body-sm text-on-surface-variant">
        <li>
            <a href="{{ route('home') }}" class="flex items-center gap-1 hover:text-primary transition-colors">
                <x-ui.icon name="home" class="text-[16px]" />
                <span>Home</span>
            </a>
        </li>

        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <x-ui.icon name="chevron_right" class="text-[14px] text-outline" />
                @if (!empty($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}" class="hover:text-primary transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-semibold text-on-surface truncate max-w-[220px] sm:max-w-none" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

