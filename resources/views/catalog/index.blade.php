@php
    $crumbs = [['label' => 'Shop / Catalogue', 'url' => url('/products')]];
    if ($selectedCategory) {
        $crumbs[] = ['label' => $selectedCategory->name];
    }
@endphp

<x-layouts.public
    :title="$metaTitle"
    :description="$metaDescription"
>
    <x-slot:head>
        <x-seo.breadcrumb-schema :items="$crumbs" />
    </x-slot:head>

    <div class="w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2">
            <x-ui.breadcrumbs :items="$crumbs" />
        </div>

        {{-- Livewire Product Catalog with Live Search & Filters --}}
        <livewire:product-catalog :initial-category="$selectedCategory?->slug" />

        {{-- Wholesale FAQ Section --}}
        <x-catalog.faq />

        {{-- Quick View Modal (Alpine) --}}
        <x-catalog.quick-view-modal />

        {{-- Fast Enquiry Modal (Livewire) --}}
        <livewire:catalog-enquiry-modal />

        {{-- Direct Paystack Payment Modal (Livewire) --}}
        <livewire:catalog-payment-modal />

        {{-- Custom Photo Request Modal (Livewire) --}}
        <livewire:custom-photo-request />
    </div>
</x-layouts.public>

