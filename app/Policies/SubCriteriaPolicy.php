<?php

namespace App\Policies;

use App\Models\SubCriteria;
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

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SubCriteria $subCriteria): bool
    {
        return $user->isAdmin();
    }
}
