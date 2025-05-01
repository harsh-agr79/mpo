<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ResourcePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Resource')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Resource $resource): bool
    {
        if ($admin->hasPermissionTo('View Resource')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Resource')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Resource $resource): bool
    {
        if ($admin->hasPermissionTo('Edit Resource')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Resource $resource): bool
    {
        if ($admin->hasPermissionTo('Delete Resource')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Resource $resource): bool
    {
        if ($admin->hasPermissionTo('Delete Resource')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Resource $resource): bool
    {
        if ($admin->hasPermissionTo('Delete Resource')) {
            return true;
        }
        return false;
    }
}
