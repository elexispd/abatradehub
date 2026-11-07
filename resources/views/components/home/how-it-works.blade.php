@php
    $steps = [
        ['n' => 1, 'tone' => 'primary', 'title' => 'Choose what you need', 'text' => 'Browse our catalogue or send us your own sample photos and references directly on WhatsApp.', 'icon' => 'photo_library', 'footer' => 'Ready styles or custom'],
        ['n' => 2, 'tone' => 'secondary', 'title' => 'Send us your order', 'text' => 'Tell us the exact quantities, sizes and colours you want. We confirm availability and give clear pricing.', 'icon' => 'receipt_long', 'footer' => 'Transparent bulk quote'],
        ['n' => 3, 'tone' => 'tertiary', 'title' => 'We source & check', 'text' => 'Our team checks quality, finishing and measurements in person before any item is packaged.', 'icon' => 'task_alt', 'footer' => 'Checked before dispatch'],
        ['n' => 4, 'tone' => 'primary', 'title' => 'We arrange delivery', 'text' => 'Safe dispatch to your store, motor park or doorstep across Nigeria and the wider African region.', 'icon' => 'local_shipping', 'footer' => 'Waybill receipt provided'],
    ];
@endphp

<section id="how-it-works" class="w-full bg-surface py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-ui.section-heading
            class="mb-16 max-w-2xl"
            align="center"
            eyebrow="Simple Sourcing Process"
            title="How It Works"
            subtitle="Buying in bulk from Aba has never been this simple."
        />

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            @foreach ($steps as $step)
                <x-ui.feature-card
                    :number="$step['n']"
                    :tone="$step['tone']"
                    :title="$step['title']"
                    :footer-icon="$step['icon']"
                    :footer="$step['footer']"
                >{{ $step['text'] }}</x-ui.feature-card>
            @endforeach
        </div>

        <div class="mt-12 flex flex-col items-center justify-between gap-6 rounded-2xl bg-surface-container p-6 md:flex-row">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary text-on-secondary">
                    <x-ui.icon name="support_agent" class="text-[24px]" />
                </div>
                <div>
                    <h3 class="text-headline-sm font-semibold text-on-surface">Have a specific design from Instagram or TikTok?</h3>
                    <p class="text-body-md text-on-surface-variant">Upload your reference photos and we'll source it from Ariaria Market.</p>
                </div>
            </div>
            <button
                type="button"
                x-data
                x-on:click="$dispatch('open-custom-photo-request')"
                class="shrink-0 inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-primary-container px-6 py-3 text-label-lg font-bold text-on-primary-container shadow-sm transition-all hover:bg-primary hover:text-on-primary"
            >
                <x-ui.icon name="add_photo_alternate" class="text-[20px]" />
                Upload Reference Photos
            </button>
        </div>

        @livewire('custom-photo-request')
    </div>
</section>

