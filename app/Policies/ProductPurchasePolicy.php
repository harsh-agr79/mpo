<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ProductsPurchase;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPurchasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Product Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, ProductsPurchase $productsPurchase): bool
    {
        if ($admin->hasPermissionTo('View Product Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Product Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, ProductsPurchase $productsPurchase): bool
    {
        if ($admin->hasPermissionTo('Edit Product Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, ProductsPurchase $productsPurchase): bool
    {
        if ($admin->hasPermissionTo('Delete Product Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, ProductsPurchase $productsPurchase): bool
    {
        if ($admin->hasPermissionTo('Delete Product Purchase')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, ProductsPurchase $productsPurchase): bool
    {
        if ($admin->hasPermissionTo('Delete Product Purchase')) {
            return true;
        }
        return false;
    }
}
