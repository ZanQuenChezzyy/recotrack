<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Auth\Access\Response;

class UserTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('View Any User Type');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserType $userType): bool
    {
        return $user->can('View User Type');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('Create User Type');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserType $userType): bool
    {
        return $user->can('Update User Type');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserType $userType): bool
    {
        return $user->can('Delete User Type');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserType $userType): bool
    {
        return $user->can('Restore User Type');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserType $userType): bool
    {
        return $user->can('Force Delete User Type');
    }
}
