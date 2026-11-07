@php
    $t = config('abatradehub.testimonial');
@endphp

@if (! empty($t['name']) && ! empty($t['quote']))
    <section class="w-full bg-surface-container py-16 lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <figure class="rounded-3xl bg-surface-container-lowest p-8 shadow-md sm:p-12 lg:p-14">
                <div class="flex items-center gap-1 text-primary" role="img" aria-label="5 out of 5 stars">
                    @for ($i = 0; $i < 5; $i++)
                        <x-ui.icon name="star" :filled="true" class="text-[20px]" />
                    @endfor
                    <span class="ml-2 text-label-sm text-on-surface-variant">5.0 Trade Review</span>
                </div>

                <blockquote class="mt-6 text-headline-md font-medium leading-relaxed text-on-surface">
                    &ldquo;{{ $t['quote'] }}&rdquo;
                </blockquote>

                <figcaption class="mt-6 flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-fixed text-headline-sm font-bold text-primary">
                        {{ \Illuminate\Support\Str::of($t['name'])->substr(0, 1)->upper() }}
                    </span>
                    <div>
                        <p class="text-headline-sm font-bold text-on-surface">{{ $t['name'] }}</p>
                        @if (! empty($t['role']))
                            <p class="text-body-md text-on-surface-variant">{{ $t['role'] }}</p>
                        @endif
                    </div>
                    <span class="ml-auto hidden items-center gap-1.5 rounded-full bg-secondary-fixed px-3 py-1 text-label-sm text-on-secondary-fixed-variant sm:inline-flex">
                        <x-ui.icon name="verified" class="text-[16px]" /> Verified Wholesale Partner
                    </span>
                </figcaption>
            </figure>
        </div>
    </section>
@endif

