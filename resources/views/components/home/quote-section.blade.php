@php
    $points = [
        'No hidden commissions or surprise park fees',
        'Video confirmation before package sealing',
        'Support available 6 days a week',
    ];
@endphp

<section id="quote" class="w-full bg-surface-container-low py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-6">
                <span class="block text-label-sm font-bold uppercase tracking-widest text-primary">Ready to Start?</span>
                <h2 class="text-display-lg-mobile font-bold text-on-surface lg:text-display-lg">Ready to place a wholesale order?</h2>
                <p class="max-w-lg text-body-lg leading-relaxed text-on-surface-variant">
                    Tell us what you&rsquo;re looking for and we&rsquo;ll help you get started today. Talk to us directly or fill the short form to get pricing.
                </p>

                <ul class="space-y-4 pt-2">
                    @foreach ($points as $point)
                        <li class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-secondary-fixed text-secondary">
                                <x-ui.icon name="check" class="text-[20px]" />
                            </span>
                            <span class="text-body-md text-on-surface">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="pt-4">
                    <x-ui.button :href="\App\Support\Whatsapp::url('Hello, I would like to place a wholesale order.')" target="_blank" rel="noopener" variant="whatsapp" size="lg" icon="chat" icon-position="leading" class="shadow-md">
                        Chat on WhatsApp ({{ \App\Support\Whatsapp::display() }})
                    </x-ui.button>
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="rounded-2xl bg-surface-container-lowest p-6 shadow-lg sm:p-10">
                    <h3 class="mb-2 text-headline-md font-bold text-on-surface">Request an Instant Quote</h3>
                    <p class="mb-6 text-body-md text-on-surface-variant">Fill in 3 short details and our sourcing desk will reach out shortly.</p>
                    <livewire:quote-form />
                </div>
            </div>
        </div>
    </div>
</section>

