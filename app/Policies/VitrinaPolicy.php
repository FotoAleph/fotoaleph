<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Vitrina;
use Illuminate\Support\Facades\Gate;

class VitrinaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'coordinador'], true);
    }

    public function view(User $user, Vitrina $vitrina): bool
    {
        return Gate::forUser($user)->allows('manage-tenant-vitrinas', $vitrina->tenant);
    }

    public function create(User $user, Tenant $tenant): bool
    {
        return Gate::forUser($user)->allows('manage-tenant-vitrinas', $tenant);
    }

    public function update(User $user, Vitrina $vitrina): bool
    {
        return Gate::forUser($user)->allows('manage-tenant-vitrinas', $vitrina->tenant);
    }

    public function delete(User $user, Vitrina $vitrina): bool
    {
        return Gate::forUser($user)->allows('manage-tenant-vitrinas', $vitrina->tenant);
    }
}
