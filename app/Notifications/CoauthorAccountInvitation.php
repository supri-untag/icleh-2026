<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class CoauthorAccountInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    /** @return array<int, string> */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $token = Password::createToken($notifiable);

        return (new MailMessage)
            ->subject('Your ICLEH co-author account')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('You have been added as a co-author. Set your password to access your ICLEH account. If you are registered as a participant, your payment bill is available in the portal.')
            ->action('Set your password', route('coauthor.account.edit', ['token' => $token, 'email' => $notifiable->email]))
            ->line('This link expires in '.config('auth.passwords.users.expire').' minutes. You can request a new link from the same page.');
    }
}
