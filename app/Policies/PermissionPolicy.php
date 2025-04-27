<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Permissions')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Permission $permission): bool
    {
        if($admin->hasPermissionTo('View Permissions')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Permissions')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Permission $permission): bool
    {
        if($admin->hasPermissionTo('Edit Permissions')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Permission $permission): bool
    {
        if($admin->hasPermissionTo('Delete Permissions')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Permission $permission): bool
    {
        if($admin->hasPermissionTo('Delete Permissions')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Permission $permission): bool
    {
        if($admin->hasPermissionTo('Delete Permissions')){
            return true;
        }
        return false;
    }
}
