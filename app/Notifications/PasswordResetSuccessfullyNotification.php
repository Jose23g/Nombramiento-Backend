<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetSuccessfullyNotification extends Notification
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
        return (new MailMessage)
            ->subject('Restablecimiento Exitoso')
            ->greeting("Hola, " . $persona->nombre . "!")
            ->line('Te informamos que el restablecimiento de tu contraseña ha sido exitoso.')
            ->line('Ahora puedes acceder a tu cuenta utilizando la nueva contraseña.')
            ->line('Detalles de la cuenta:')
            ->line('Correo Electrónico: ' . $notifiable->correo)
            ->line('¡Gracias por usar nuestra aplicación!')
            ->line('Atentamente:')
            ->salutation('El equipo de SandUCR!');
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
