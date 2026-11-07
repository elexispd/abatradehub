<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_user');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->can('view_user');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_user');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Nobody can edit their own roles or status from this module
        if ($user->id === $model->id) {
            return false;
        }

        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->can('update_user');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Nobody can delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        // Non-super-admins cannot delete super-admins
        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        // Last active super-admin cannot be deleted
        if ($model->isSuperAdmin() && self::isLastSuperAdmin($model)) {
            return false;
        }

        return $user->can('delete_user');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->can('restore_user');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        if ($model->isSuperAdmin() && self::isLastSuperAdmin($model)) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can disable/enable the model.
     */
    public function disable(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        if ($model->isSuperAdmin() && self::isLastSuperAdmin($model)) {
            return false;
        }

        return $user->can('disable_user');
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRoles(User $user): bool
    {
        return $user->can('assign_roles');
    }

    /**
     * Determine whether the user can assign the super_admin role.
     */
    public function assignSuperAdmin(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can reset the model's password.
     */
    public function resetPassword(User $user, User $model): bool
    {
        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->can('reset_user_password');
    }

    /**
     * Check if a given super admin is the last active super admin in the system.
     */
    public static function isLastSuperAdmin(User $user): bool
    {
        if (! $user->isSuperAdmin()) {
            return false;
        }

        return User::role('super_admin')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->count() <= 1;
    }
}

