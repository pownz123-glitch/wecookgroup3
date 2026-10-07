<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkOrder $workOrder): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if (in_array($user->role, ['admin', 'operations', 'supervisor'], true)) {
            return true;
        }

        return $user->role === 'field'
            && $workOrder->assignedPersonnel()->whereKey($user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['admin', 'supervisor'], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $user->is_active
            && $user->role === 'field'
            && $workOrder->status === 'in_progress'
            && $workOrder->assignedPersonnel()->whereKey($user->id)->exists();
    }

    public function assign(User $user, WorkOrder $workOrder): bool
    {
        return $user->is_active && in_array($user->role, ['admin', 'supervisor'], true);
    }

    public function start(User $user, WorkOrder $workOrder): bool
    {
        return $user->is_active
            && $user->role === 'field'
            && $workOrder->status === 'assigned'
            && $workOrder->assignedPersonnel()->whereKey($user->id)->exists();
    }

    public function submit(User $user, WorkOrder $workOrder): bool
    {
        return $this->update($user, $workOrder);
    }

    public function review(User $user, WorkOrder $workOrder): bool
    {
        return $user->is_active && in_array($user->role, ['admin', 'supervisor'], true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->is_active && $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WorkOrder $workOrder): bool
    {
        return $this->delete($user, $workOrder);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WorkOrder $workOrder): bool
    {
        return $this->delete($user, $workOrder);
    }
}
