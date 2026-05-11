<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RappelCpnNotification extends Notification
{
    use Queueable;

    protected $rdv;

    public function __construct($rdv)
    {
        $this->rdv = $rdv;
    }

    // On définit les canaux : 'database' pour la cloche, 'mail' pour l'email
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

   // Dans RappelCpnNotification.php

public function toMail($notifiable)
{
    // On récupère l'adresse de React depuis le fichier .env
    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

    return (new MailMessage)
        ->subject('Rappel : Votre consultation prénatale (CPN)')
        ->greeting('Bonjour Mme ' . $notifiable->nom) // <-- Corrigé ici
        ->line('Ceci est un rappel pour votre consultation n°' . $this->rdv->cpn_number . '.')
        ->line('Elle est prévue le ' . \Carbon\Carbon::parse($this->rdv->scheduled_at)->format('d/m/Y à H:i') . '.')
        // On envoie la maman vers React (port 5173) et non vers Laravel
        ->action('Voir mon carnet', $frontendUrl . '/patient/dashboard') 
        ->line('Merci de votre confiance.');
}

    // Contenu pour la Cloche (Base de données)
    public function toArray($notifiable)
    {
        return [
            'title' => 'Rappel CPN n°' . $this->rdv->cpn_number,
            'message' => 'Votre rendez-vous est prévu dans 2 jours.',
            'type' => 'rappel_cpn',
            'appointment_id' => $this->rdv->id
        ];
    }
}