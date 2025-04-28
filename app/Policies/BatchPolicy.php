<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Batch;
use Illuminate\Auth\Access\Response;

class BatchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Batches')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Batch $batch): bool
    {
        if ($admin->hasPermissionTo('View Batches')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Batches')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Batch $batch): bool
    {
        if ($admin->hasPermissionTo('Edit Batches')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Batch $batch): bool
    {
        if ($admin->hasPermissionTo('Delete Batches')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Batch $batch): bool
    {
        if ($admin->hasPermissionTo('Delete Batches')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Batch $batch): bool
    {
        if ($admin->hasPermissionTo('Delete Batches')) {
            return true;
        }
        return false;
    }
}
