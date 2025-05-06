<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\SubCategory;
use Illuminate\Auth\Access\Response;

class SubCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Sub Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, SubCategory $subCategory): bool
    {
        if ($admin->hasPermissionTo('View Sub Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Sub Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, SubCategory $subCategory): bool
    {
        if ($admin->hasPermissionTo('Edit Sub Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, SubCategory $subCategory): bool
    {
        if ($admin->hasPermissionTo('Delete Sub Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, SubCategory $subCategory): bool
    {
        if ($admin->hasPermissionTo('Restore Sub Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, SubCategory $subCategory): bool
    {
        if ($admin->hasPermissionTo('Force Delete Sub Categories')) {
            return true;
        }
        return false;
    }
}
