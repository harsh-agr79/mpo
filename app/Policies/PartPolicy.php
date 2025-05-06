<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Part;
use Illuminate\Auth\Access\Response;

class PartPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('View Part')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Admin $admin, Part $part): bool
    {
        if ($admin->hasPermissionTo('View Part')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Admin $admin): bool
    {
        if ($admin->hasPermissionTo('Create Part')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Admin $admin, Part $part): bool
    {
        if ($admin->hasPermissionTo('Edit Part')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, Part $part): bool
    {
        if ($admin->hasPermissionTo('Delete Part')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, Part $part): bool
    {
        if ($admin->hasPermissionTo('Restore Part')) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, Part $part): bool
    {
        if ($admin->hasPermissionTo('Force Delete Part')) {
            return true;
        }
        return false;
    }
}
