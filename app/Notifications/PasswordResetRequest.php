<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token, $urlFront;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token, $urlFront)
    {
        $this->token = $token;
        $this->urlFront = $urlFront;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // url entra a rota do frontend do formulário de resetar senha
        // passando como parametro na url o token www.front.com/reset?token=$this->token
        //$url = url('/api/password/find/'.$this->token);
        $url = $this->urlFront.'/'.$this->token;
        return (new MailMessage)
            ->subject('Alterar Senha - DuckLegendas')
            ->greeting('Olá!')
            ->line('Você está recebendo este e-mail pois recebemos uma requisição para troca de senha em sua conta.')
            ->action('Trocar senha', url($url))
            ->line('Se você não requisitou a troca de senha em sua conta, nenhuma ação é necessária.')
            ->salutation("Atenciosamente, ducklegendas.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
