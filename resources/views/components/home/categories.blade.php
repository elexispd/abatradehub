@props(['categories'])

@if ($categories->isNotEmpty())
    <section id="categories" class="w-full bg-surface-container-low py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <x-ui.section-heading
                    eyebrow="Fast-Selling Inventories"
                    title="Explore Wholesale Categories"
                    subtitle="Fast-moving products from Aba, ready for your store."
                />
                <a href="{{ url('/products') }}" class="inline-flex min-h-11 items-center gap-2 text-label-lg text-primary hover:underline">
                    <span>See all available products</span>
                    <x-ui.icon name="arrow_forward" class="text-[18px]" />
                </a>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($categories as $category)
                    <x-home.category-card :category="$category" />
                @endforeach
            </div>
        </div>
    </section>
@endif

