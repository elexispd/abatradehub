<x-layouts.public
    :title="config('abatradehub.tagline')"
    :description="config('abatradehub.description')"
>
    <x-slot:head>
        <x-seo.organization-schema />
    </x-slot:head>

    <div class="flex w-full flex-col">
        <x-home.hero :hero-products="$heroProducts" :min-moq="$minMoq" />
        <x-home.how-it-works />
        <x-home.categories :categories="$categories" />
        <x-home.why-us />
        <x-home.testimonial />
        <x-home.quote-section />
    </div>
</x-layouts.public>

