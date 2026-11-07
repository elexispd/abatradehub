<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function show(Sale $sale)
    {
        Gate::authorize('view', $sale);

        $sale->load([
            'items.product',
            'payments.recorder',
            'creator',
            'enquiry',
        ]);

        return view('filament.sales.invoice', [
            'sale' => $sale,
        ]);
    }
}

