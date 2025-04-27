<?php

namespace App\Policies;

use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class AdminPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Admins')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Admins')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Admins')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Edit Admins')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Delete Admins')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Delete Admins')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Delete Admins')){
            return true;
        }
        return false;
    }
}
