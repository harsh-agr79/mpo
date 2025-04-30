<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Payments')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Payment $payment): bool
    {
        if ($admin->hasPermissionTo('View Payments')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Payments')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Payment $payment): bool
    {
        if ($admin->hasPermissionTo('Edit Payments')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Payment $payment): bool
    {
        if ($admin->hasPermissionTo('Delete Payments')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Payment $payment): bool
    {
        if ($admin->hasPermissionTo('Delete Payments')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Payment $payment): bool
    {
        if ($admin->hasPermissionTo('Delete Payments')) {
            return true;
        }
        return false;
    }
}
