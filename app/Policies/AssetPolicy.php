<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['admin', 'operations', 'supervisor'], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Asset $asset): bool
    {
        return $user->is_active && (
            in_array($user->role, ['admin', 'operations', 'supervisor'], true)
            || ($user->role === 'field' && $asset->workOrders()->whereHas('assignedPersonnel', fn ($query) => $query->whereKey($user->id))->exists())
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['admin', 'operations'], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Asset $asset): bool
    {
        return $user->is_active && in_array($user->role, ['admin', 'operations'], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Asset $asset): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Asset $asset): bool
    {
        return $this->delete($user, $asset);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Asset $asset): bool
    {
        return $this->delete($user, $asset);
    }
}
