<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function redesSociales()
    {
        return $this->morphMany(SocialNetwork::class, 'socialable');
    }

    public function aleatoriasRedesSociales()
    {
        return $this->morphMany(SocialNetwork::class, 'socialable')->inRandomOrder()->limit(2);
    }

    public function tenant()
    {
        return $this->belongsToMany(Tenant::class);
    }

    // Métodos helper para roles
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente';
    }

    public function isEmpleado(): bool
    {
        return $this->role === 'empleado';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
