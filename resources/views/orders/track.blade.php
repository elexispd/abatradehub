@php
    $crumbs = [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Track Order'],
    ];
@endphp

<x-layouts.public
    title="Track Wholesale Order & Shipment"
    description="Track your AbaTradeHub wholesale order in real time. View pre-shipment quality inspection, packaging, and commercial park waybill transit."
>
    <x-slot:head>
        <x-seo.breadcrumb-schema :items="$crumbs" />
    </x-slot:head>

    <div class="w-full pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 pb-6">
            <x-ui.breadcrumbs :items="$crumbs" />
        </div>

        <livewire:track-order
            :initial-invoice="request('invoice', request('order', ''))"
            :initial-phone="request('phone', '')"
        />
    </div>
</x-layouts.public>

