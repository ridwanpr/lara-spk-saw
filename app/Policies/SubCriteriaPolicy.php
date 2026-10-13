<?php

namespace App\Policies;

use App\Models\User;

class SubCriteriaPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }
}
