<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Uri;

class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct(#[\SensitiveParameter] string $token)
    {
        parent::__construct($token);

        $this->afterCommit();
    }

    protected function resetUrl($notifiable): string
    {
        return Uri::of(config('app.frontend_url').'/reset-password')
            ->withQuery([
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ])
            ->value();
    }
}
