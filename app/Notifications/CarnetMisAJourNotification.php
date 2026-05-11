<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CarnetMisAJourNotification extends Notification
{
    use Queueable;

    protected $cpnNumber;

    public function __construct($cpnNumber)
    {
        $this->cpnNumber = $cpnNumber;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Multicanal : Cloche + Email
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('E-CPN : Votre carnet numérique a été mis à jour')
            ->greeting('Bonjour Mme ' . $notifiable->nom)
            ->line('Le compte-rendu de votre consultation prénatale n°' . $this->cpnNumber . ' est disponible.')
            ->line('Vous pouvez consulter vos constantes, les mesures de votre bébé et télécharger vos résultats d\'examens directement dans votre application.')
            ->action('Voir mon carnet', url('/dashboard/rendezvous'))
            ->line('Prenez soin de vous et de votre bébé.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Compte-rendu CPN n°' . $this->cpnNumber,
            'message' => 'Les résultats de votre visite sont disponibles dans votre carnet numérique.',
            'type' => 'suivi', // Type suivi pour le design
            'cpn_number' => $this->cpnNumber
        ];
    }
}