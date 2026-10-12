<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

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
        ];
    }

    /**
     * Get the role associated with the user.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get repair tickets reported by this user.
     *
     * @return HasMany<RepairTicket, $this>
     */
    public function reportedTickets(): HasMany
    {
        return $this->hasMany(RepairTicket::class, 'reporter_id');
    }

    /**
     * Get repair tickets verified by this user.
     *
     * @return HasMany<RepairTicket, $this>
     */
    public function verifiedTickets(): HasMany
    {
        return $this->hasMany(RepairTicket::class, 'verifier_id');
    }

    /**
     * Determine if the user has a specific role or any of the specified roles.
     *
     * @param  RoleName|string|array<RoleName|string>  $roles
     */
    public function hasRole(RoleName|string|array $roles): bool
    {
        if (! $this->role) {
            return false;
        }

        $currentRoleValue = $this->role->name instanceof RoleName
            ? $this->role->name->value
            : (string) $this->role->name;

        $targetRoles = is_array($roles) ? $roles : [$roles];

        foreach ($targetRoles as $targetRole) {
            $targetValue = $targetRole instanceof RoleName
                ? $targetRole->value
                : (string) $targetRole;

            if (strtolower($currentRoleValue) === strtolower($targetValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user is an Admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(RoleName::Admin);
    }

    /**
     * Check if the user is an Inspector.
     */
    public function isInspector(): bool
    {
        return $this->hasRole(RoleName::Inspector);
    }

    /**
     * Check if the user is Staff.
     */
    public function isStaff(): bool
    {
        return $this->hasRole(RoleName::Staff);
    }
}
