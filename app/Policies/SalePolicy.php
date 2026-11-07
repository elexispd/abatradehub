<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Sale;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SalePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Sale') || $authUser->can('View:Sale');
    }

    public function view(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('View:Sale') || $authUser->can('ViewAny:Sale');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Sale');
    }

    public function update(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('Update:Sale');
    }

    public function delete(AuthUser $authUser, Sale $sale): bool
    {
        // A paid sale or sale with payments recorded cannot be deleted
        if ($sale->payment_status === \App\Enums\PaymentStatus::Paid || $sale->amount_paid > 0) {
            return false;
        }

        return $authUser->can('Delete:Sale');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Sale');
    }

    public function restore(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('Restore:Sale');
    }

    public function forceDelete(AuthUser $authUser, Sale $sale): bool
    {
        // A paid sale or sale with payments recorded cannot be force deleted
        if ($sale->payment_status === \App\Enums\PaymentStatus::Paid || $sale->amount_paid > 0) {
            return false;
        }

        return $authUser->can('ForceDelete:Sale');
    }

    public function printInvoice(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('print_invoice') || $authUser->can('View:Sale') || $authUser->can('ViewAny:Sale');
    }

    public function recordPayment(AuthUser $authUser, Sale $sale): bool
    {
        return $authUser->can('record_payment') || $authUser->can('Update:Sale');
    }
}

