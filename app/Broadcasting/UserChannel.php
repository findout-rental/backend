<?php

namespace App\Broadcasting;

use App\Models\User;

class UserChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     * Users can only join their own private channel.
     */
    public function join(User $user, int $userId): array|bool
    {
        // User can only access their own channel
        return $user->id === $userId;
    }
}
