<?php

namespace App\Policies;

use App\Models\Cotizacion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CotizacionPolicy
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
    public function view(User $user, Cotizacion $cotizacion): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'cliente' && $cotizacion->user_id === $user->id;
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
    public function update(User $user, Cotizacion $cotizacion): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cotizacion $cotizacion): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cotizacion $cotizacion): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cotizacion $cotizacion): bool
    {
        return false;
    }
}
