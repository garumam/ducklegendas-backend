<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;

class VerifyEmail extends VerifyEmailBase
{

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return (new MailMessage)
            ->subject('E-mail de verificação')
            ->line('Por favor clique no botão abaixo para verificar seu e-mail!')
            ->action('Verificar e-mail', $verificationUrl)
            ->line('Se você não criou uma conta nenhuma ação é necessária.');
    }

    /**
    * Get the verification URL for the given notifiable.
    *
    * @param mixed $notifiable
    * @return string
    */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
        "verification.verify", Carbon::now()->addMinutes(60), ["id" => $notifiable->getKey()]
        ); // this will basically mimic the email endpoint with get request
    }
}
