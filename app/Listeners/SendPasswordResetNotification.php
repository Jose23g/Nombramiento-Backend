<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;

class SendPasswordResetNotification
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\PasswordReset $event
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        $event->user->sendPasswordResetSuccessfullyNotification();
    }
}
