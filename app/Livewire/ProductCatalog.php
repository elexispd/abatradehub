<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'cat', history: true)]
    public string $categorySlug = '';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'sort', history: true)]
    public string $sort = 'default'; // default | price-low | price-high | moq-low

    #[Url(as: 'price', history: true)]
    public string $priceRange = ''; // under15 | 15to25 | above25

    public int $perPage = 12;

    public function mount(?string $initialCategory = null): void
    {
        if ($initialCategory && ! $this->categorySlug) {
            $this->categorySlug = $initialCategory;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategorySlug(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function updatingPriceRange(): void
    {
        $this->resetPage();
    }

    public function selectCategory(string $slug = ''): void
    {
        $this->categorySlug = $slug;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['categorySlug', 'search', 'sort', 'priceRange']);
        $this->resetPage();
    }

    public function render()
    {
        $publishedScope = fn ($query) => $query->published();

        $categories = Category::query()
            ->active()
            ->whereHas('products', $publishedScope)
            ->withCount(['products as published_products_count' => $publishedScope])
            ->orderBy('sort_order')
            ->get();

        $query = Product::published()
            ->with(['category', 'media', 'priceTiers', 'supplier']);

        // Filter by category
        if ($this->categorySlug !== '') {
            $query->whereHas('category', function (Builder $q) {
                $q->where('slug', $this->categorySlug);
            });
        }

        // Search in name, sku, description
        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Price range filter
        if ($this->priceRange !== '') {
            $query->where(function (Builder $q) {
                match ($this->priceRange) {
                    'under15' => $q->where(function ($sub) {
                        $sub->where('base_price', '<', 15000)
                            ->orWhereHas('priceTiers', fn ($t) => $t->where('unit_price', '<', 15000));
                    }),
                    '15to25' => $q->where(function ($sub) {
                        $sub->whereBetween('base_price', [15000, 25000])
                            ->orWhereHas('priceTiers', fn ($t) => $t->whereBetween('unit_price', [15000, 25000]));
                    }),
                    'above25' => $q->where(function ($sub) {
                        $sub->where('base_price', '>', 25000)
                            ->orWhereHas('priceTiers', fn ($t) => $t->where('unit_price', '>', 25000));
                    }),
                    default => null,
                };
            });
        }

        // Sorting
        match ($this->sort) {
            'price-low' => $query->orderByRaw('COALESCE(base_price, (SELECT MIN(unit_price) FROM price_tiers WHERE price_tiers.product_id = products.id)) ASC'),
            'price-high' => $query->orderByRaw('COALESCE(base_price, (SELECT MAX(unit_price) FROM price_tiers WHERE price_tiers.product_id = products.id)) DESC'),
            'moq-low' => $query->orderBy('moq', 'asc')->orderBy('name', 'asc'),
            default => $query->orderByDesc('is_featured')->latest('published_at')->latest('id'),
        };

        $products = $query->paginate($this->perPage);

        return view('livewire.product-catalog', [
            'products' => $products,
            'categories' => $categories,
            'hasActiveFilters' => $this->search !== '' || $this->categorySlug !== '' || $this->priceRange !== '' || $this->sort !== 'default',
        ]);
    }
}

