<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\ProductsPurchaseAdjustment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductsPurchaseAdjustmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Product Purchase Adjustment')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, ProductsPurchaseAdjustment $productsPurchaseAdjustment): bool
    {
        if ($admin->hasPermissionTo('View Product Purchase Adjustment')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Product Purchase Adjustment')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, ProductsPurchaseAdjustment $productsPurchaseAdjustment): bool
    {
        if ($admin->hasPermissionTo('Edit Product Purchase Adjustment')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, ProductsPurchaseAdjustment $productsPurchaseAdjustment): bool
    {
        if ($admin->hasPermissionTo('Delete Product Purchase Adjustment')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, ProductsPurchaseAdjustment $productsPurchaseAdjustment): bool
    {
        if ($admin->hasPermissionTo('Restore Product Purchase Adjustment')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, ProductsPurchaseAdjustment $productsPurchaseAdjustment): bool
    {
        if ($admin->hasPermissionTo('Force Delete Product Purchase Adjustment')) {
            return true;
        }
        return false;
    }
}
