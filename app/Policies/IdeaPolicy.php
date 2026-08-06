<?php

namespace App\Policies;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IdeaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function workWith(User $user, Idea $idea): bool
    {
        return $idea->user->is($user);
    }
}
