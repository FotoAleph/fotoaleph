<?php

namespace App\Policies;

use App\Models\Pqr;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PqrPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['cliente', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pqr $pqr): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'cliente' && $pqr->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'cliente';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pqr $pqr): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pqr $pqr): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pqr $pqr): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pqr $pqr): bool
    {
        return false;
    }
}
