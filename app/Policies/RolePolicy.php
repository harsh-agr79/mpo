<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if($admin->hasPermissionTo('View Roles')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Role $role): bool
    {
        if($admin->hasPermissionTo('View Roles')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if($admin->hasPermissionTo('Create Roles')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Role $role): bool
    {
        if($admin->hasPermissionTo('Edit Roles')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Role $role): bool
    {
        if($admin->hasPermissionTo('Delete Roles')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Role $role): bool
    {
        if($admin->hasPermissionTo('Delete Roles')){
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Role $role): bool
    {
        if($admin->hasPermissionTo('Delete Roles')){
            return true;
        }
        return false;
    }
}
