<?php

namespace Statamic\Actions;

use Statamic\Contracts\Auth\User as UserContract;

class SendPasswordReset extends Action
{
    public function filter($item)
    {
        return $item instanceof UserContract;
    }

    public function authorize($authed, $user)
    {
        return $authed->can('sendPasswordReset', $user);
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Send password reset email to this user?|Send password reset email to these :count users?';
    }

    public function buttonText()
    {
        /** @translation */
        return 'Send|Send to :count users';
    }

    public function run($users)
    {
        $users->each->generateTokenAndSendPasswordResetNotification();
    }
}
