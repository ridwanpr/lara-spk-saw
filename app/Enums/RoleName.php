<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case Inspector = 'inspector';
    case Staff = 'staff';

    /**
     * Get the human-readable display name for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Inspector => 'Inspector',
            self::Staff => 'Staff',
        };
    }
}
