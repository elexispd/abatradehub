<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function view(AuthUser $authUser, Role $role): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function update(AuthUser $authUser, Role $role): bool
    {
        // super_admin role cannot be edited
        if ($role->name === 'super_admin') {
            return false;
        }

        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function delete(AuthUser $authUser, Role $role): bool
    {
        // super_admin role cannot be deleted
        if ($role->name === 'super_admin') {
            return false;
        }

        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function restore(AuthUser $authUser, Role $role): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function forceDelete(AuthUser $authUser, Role $role): bool
    {
        if ($role->name === 'super_admin') {
            return false;
        }

        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function replicate(AuthUser $authUser, Role $role): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser instanceof User && $authUser->isSuperAdmin();
    }
}
