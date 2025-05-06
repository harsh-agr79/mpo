<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Expenses')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Expense $expense): bool
    {
        if ($admin->hasPermissionTo('View Expenses')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Expenses')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Expense $expense): bool
    {
        if ($admin->hasPermissionTo('Edit Expenses')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Expense $expense): bool
    {
        if ($admin->hasPermissionTo('Delete Expenses')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Expense $expense): bool
    {
        if ($admin->hasPermissionTo('Restore Expenses')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Expense $expense): bool
    {
        if ($admin->hasPermissionTo('Force Delete Expenses')) {
            return true;
        }
        return false;
    }
}
