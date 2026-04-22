<?php

namespace App\Policies;

use App\Models\TransactionType;
use App\Models\User;

class TransactionTypePolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, TransactionType $transactionType): bool
    {
        return $user->hasRole('admin');
    }
}

