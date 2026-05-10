<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    // URL firmada válida por 60 minutos
    protected function buildVerificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->buildVerificationUrl($notifiable);

        return (new MailMessage)
            ->subject('✉️ Verifica tu correo — ' . config('app.name'))
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line('Para activar tu cuenta debes verificar tu dirección de correo electrónico.')
            ->line('Este enlace expirará en **60 minutos**.')
            ->action('Verificar correo electrónico', $verificationUrl)
            ->line('Si no creaste una cuenta, puedes ignorar este correo.')
            ->salutation('Saludos, ' . config('app.name') . ' — EP Ingeniería de Sistemas');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
