<?php

namespace App\Policies;

use App\Models\Evento;
use App\Models\Tenant;
use App\Models\User;

class CasaAngelEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()
            || $this->isCasaAngelCoordinator($user)
            || $user->isCliente();
    }

    public function view(User $user, Evento $evento): bool
    {
        return $user->isAdmin()
            || $this->isCasaAngelCoordinator($user)
            || ($user->isCliente() && $evento->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Evento $evento): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Evento $evento): bool
    {
        return $user->isAdmin();
    }

    private function isCasaAngelCoordinator(User $user): bool
    {
        return $user->isCoordinador()
            && $user->tenant()
                ->where('database_connection', 'tenant_casa_angel')
                ->exists();
    }
}