<?php

namespace App\Livewire;

use App\Services\BasketService;
use Livewire\Attributes\On;
use Livewire\Component;

class BasketCounter extends Component
{
    public int $count = 0;
    public int $totalUnits = 0;

    public function mount(BasketService $basket): void
    {
        $this->refreshStats($basket);
    }

    #[On('basket-updated')]
    public function onBasketUpdated(BasketService $basket): void
    {
        $this->refreshStats($basket);
    }

    protected function refreshStats(BasketService $basket): void
    {
        $this->count = $basket->count();
        $this->totalUnits = $basket->totalUnits();
    }

    public function openBasket(): void
    {
        $this->dispatch('open-basket');
    }

    public function render()
    {
        return view('livewire.basket-counter');
    }
}

