<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Category $category): bool
    {
        if ($admin->hasPermissionTo('View Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Category $category): bool
    {
        if ($admin->hasPermissionTo('Edit Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Category $category): bool
    {
        if ($admin->hasPermissionTo('Delete Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Category $category): bool
    {
        if ($admin->hasPermissionTo('Delete Categories')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Category $category): bool
    {
        if ($admin->hasPermissionTo('Delete Categories')) {
            return true;
        }
        return false;
    }
}
