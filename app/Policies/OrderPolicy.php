<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Order $order): bool
    {
         return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Order $order): bool
    {
        if (!$admin->hasPermissionTo('Order View First') &&
            ($order->seenby === null || $order->seenAdmin === null)) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Order $order): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Order $order): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Order $order): bool
    {
        return true;
    }
}
