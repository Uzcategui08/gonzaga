<?php

namespace App\Policies;

use App\Models\Pase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PasePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function view(User $user)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Pase $pase)
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Pase $pase)
    {
        return $user->hasRole('admin');
    }
}
