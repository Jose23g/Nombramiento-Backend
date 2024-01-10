<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $persona = $notifiable->persona;
        $nombreCompleto = explode(' ', $persona->nombre);
        $primerNombre = $nombreCompleto[0];
        return (new MailMessage)
            ->subject('Verificación de correo electrónico')
            ->greeting("Hola, " . $primerNombre . "!")
            ->line('Gracias por registrarte en nuestra aplicación.')
            ->line('Para continuar, por favor verifica tu dirección de correo electrónico.')
            ->action('Verificar Correo', $this->verificationUrl($notifiable))
            ->line('¡Gracias por usar nuestra aplicación!')
            ->line('Atentamente:')
            ->salutation('El equipo de SandUCR!');
    }

    protected function verificationUrl($notifiable)
    {
        return route('verification.verify', [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ]);
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
