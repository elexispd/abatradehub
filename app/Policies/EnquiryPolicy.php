<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Enquiry;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnquiryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Enquiry') || $authUser->can('View:Enquiry');
    }

    public function view(AuthUser $authUser, Enquiry $enquiry): bool
    {
        return $authUser->can('View:Enquiry') || $authUser->can('ViewAny:Enquiry');
    }

    public function changeStatus(AuthUser $authUser, Enquiry $enquiry): bool
    {
        return $authUser->can('change_enquiry_status');
    }

    public function assign(AuthUser $authUser, Enquiry $enquiry): bool
    {
        return $authUser->can('assign_enquiry');
    }

    public function export(AuthUser $authUser): bool
    {
        return $authUser->can('export_enquiries');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Enquiry');
    }

    public function update(AuthUser $authUser, Enquiry $enquiry): bool
    {
        return $authUser->can('Update:Enquiry');
    }

    public function delete(AuthUser $authUser, Enquiry $enquiry): bool
    {
        return $authUser->can('Delete:Enquiry');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Enquiry');
    }

    public function restore(AuthUser $authUser, Enquiry $enquiry): bool
    {
        return $authUser->can('Restore:Enquiry');
    }

    public function forceDelete(AuthUser $authUser, Enquiry $enquiry): bool
    {
        return $authUser->can('ForceDelete:Enquiry');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Enquiry');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Enquiry');
    }

    public function replicate(AuthUser $authUser, Enquiry $enquiry): bool
    {
        return $authUser->can('Replicate:Enquiry');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Enquiry');
    }

}