@php
    $benefits = [
        ['icon' => 'groups', 'tone' => 'secondary', 'title' => 'Trusted Supplier Network', 'text' => 'We work with vetted, reliable manufacturers and traders who deliver consistent sizing, neat finishing and dependable colour matching.', 'footerIcon' => 'verified', 'footer' => 'Supplier screening'],
        ['icon' => 'fact_check', 'tone' => 'primary', 'title' => 'Quality Checks Before Delivery', 'text' => 'Every order is inspected before it is dispatched, so you receive goods you can sell immediately.', 'footerIcon' => 'check_box', 'footer' => 'Itemised verification'],
        ['icon' => 'shopping_bag', 'tone' => 'tertiary', 'title' => 'Wholesale & Bulk Friendly', 'text' => 'Low starter minimums so small retailers and new vendors can test products without tying down too much cash.', 'footerIcon' => 'payments', 'footer' => 'Low MOQs'],
        ['icon' => 'forum', 'tone' => 'secondary', 'title' => 'Support Throughout Your Order', 'text' => 'Talk directly to your sourcing manager on WhatsApp anytime you need updates, video samples or packing adjustments.', 'footerIcon' => 'schedule', 'footer' => 'Fast daily WhatsApp responses'],
    ];
    $deliveryCities = ['Lagos (Parks & Doorstep)', 'Abuja', 'Port Harcourt', 'Accra (Ghana)', 'All Interstate Parks'];
@endphp

<section id="why-us" class="w-full bg-surface py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-ui.section-heading
            class="mb-14 max-w-2xl"
            tone="secondary"
            eyebrow="Reliable Wholesale Operations"
            title="Why Retailers Trust Us"
            subtitle="We take the stress out of travelling to crowded markets, saving you transport fares and time."
        />

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($benefits as $b)
                <x-ui.feature-card
                    padding="p-8"
                    :icon="$b['icon']"
                    :tone="$b['tone']"
                    :title="$b['title']"
                    :footer-icon="$b['footerIcon']"
                    :footer="$b['footer']"
                >{{ $b['text'] }}</x-ui.feature-card>
            @endforeach

            <x-ui.feature-card class="lg:col-span-2" padding="p-8" icon="local_shipping" tone="primary" title="Reliable Delivery Arrangements">
                We pack securely in heavy-duty sacks or cartons and handle waybills directly to commercial transport parks across Nigeria, plus air freight and regional shipping.
                <x-slot:extra>
                    <div class="mt-6 flex flex-wrap gap-2 pt-2">
                        @foreach ($deliveryCities as $city)
                            <span class="rounded bg-surface px-2.5 py-1 text-label-sm text-on-surface-variant">{{ $city }}</span>
                        @endforeach
                    </div>
                </x-slot:extra>
            </x-ui.feature-card>
        </div>
    </div>
</section>

