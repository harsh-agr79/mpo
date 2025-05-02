<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\PartsPurchase;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PartsPurchasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Parts Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, PartsPurchase $partsPurchase): bool
    {
        if ($admin->hasPermissionTo('View Parts Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Parts Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, PartsPurchase $partsPurchase): bool
    {
        if ($admin->hasPermissionTo('Edit Parts Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, PartsPurchase $partsPurchase): bool
    {
        if ($admin->hasPermissionTo('Delete Parts Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, PartsPurchase $partsPurchase): bool
    {
        if ($admin->hasPermissionTo('Delete Parts Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, PartsPurchase $partsPurchase): bool
    {
        if ($admin->hasPermissionTo('Delete Parts Purchase')) {
            return true;
        }
        return false;
    }
}
