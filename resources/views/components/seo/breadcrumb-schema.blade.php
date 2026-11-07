@props([
    'items' => [],
])

@php
    $elements = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => route('home'),
        ],
    ];

    $pos = 2;
    foreach ($items as $item) {
        $element = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => $item['label'],
        ];

        if (!empty($item['url'])) {
            $element['item'] = $item['url'];
        }

        $elements[] = $element;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $elements,
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

