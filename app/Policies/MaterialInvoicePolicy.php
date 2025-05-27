<?php

namespace App\Policies;

use App\Models\MaterialInvoice;
use App\Models\Admin;
use Illuminate\Auth\Access\Response;

class MaterialInvoicePolicy
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
    public function view(Admin $admin, MaterialInvoice $materialInvoice): bool
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
    public function update(Admin $admin, MaterialInvoice $materialInvoice): bool
    {
        if (!$admin->hasPermissionTo('Material View First') &&
            ($materialInvoice->seenby === null || $materialInvoice->seenAdmin === null)) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Admin $admin, MaterialInvoice $materialInvoice): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Admin $admin, MaterialInvoice $materialInvoice): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Admin $admin, MaterialInvoice $materialInvoice): bool
    {
        return true;
    }
}
