<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InventoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Inventory $inventory): bool
    {
        return $user->id === $inventory->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->inventories()->count() < 4 || $user->role == 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function kemaskini(User $user, Inventory $inventory): bool
    {
        return $user->id === $inventory->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function padam(User $user, Inventory $inventory): bool
    {
        return $user->role == 'admin' || $user->id === $inventory->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Inventory $inventory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Inventory $inventory): bool
    {
        return false;
    }
}
