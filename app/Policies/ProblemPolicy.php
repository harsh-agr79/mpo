<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProblemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Problem')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Problem $problem): bool
    {
        if ($admin->hasPermissionTo('View Problem')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Problem')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Problem $problem): bool
    {
        if ($admin->hasPermissionTo('Edit Problem')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Problem $problem): bool
    {
        if ($admin->hasPermissionTo('Delete Problem')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Problem $problem): bool
    {
        if ($admin->hasPermissionTo('Delete Problem')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Problem $problem): bool
    {
        if ($admin->hasPermissionTo('Delete Problem')) {
            return true;
        }
        return false;
    }
}
