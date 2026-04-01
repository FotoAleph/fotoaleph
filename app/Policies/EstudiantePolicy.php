<?php

namespace App\Policies;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class EstudiantePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || Gate::forUser($user)->allows('manage-sport-bogota-estudiantes');
    }

    public function view(User $user, Estudiante $estudiante): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Estudiante $estudiante): bool
    {
        return $user->role === 'admin' || Gate::forUser($user)->allows('manage-sport-bogota-estudiantes');
    }

    public function delete(User $user, Estudiante $estudiante): bool
    {
        return $user->role === 'admin';
    }
}
