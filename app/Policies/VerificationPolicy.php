<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Verification;

class VerificationPolicy
{
    public function view(User $user, Verification $verification): bool
    {
        return $user->isAdmin() || $user->is($verification->user);
    }

    public function update(User $user, Verification $verification): bool
    {
        return $user->isAdmin() || $user->is($verification->user);
    }
}
