<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAnyIntern(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function createIntern(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function updateIntern(User $user, User $intern): bool
    {
        return $user->hasRole('admin') && $intern->hasRole('intern');
    }

    public function deleteIntern(User $user, User $intern): bool
    {
        return $user->hasRole('admin') && $intern->hasRole('intern');
    }
}

