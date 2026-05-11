<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Notifications\RappelCpnNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RemindCPN extends Command
{
    // Nom de la commande à taper dans le terminal
    protected $signature = 'cpn:send-reminders';
    protected $description = 'Envoie des rappels par mail et cloche 2 jours avant la CPN';

    public function handle()
    {
        // 1. Définir la date cible (dans 2 jours)
        $targetDate = Carbon::now()->addDays(2)->toDateString();

        // 2. Récupérer les RDV prévus à cette date, non notifiés, avec les infos de la patiente
        // Note: On utilise 'patient' qui est la relation dans ton modèle Appointment
        $appointments = Appointment::with('patient')
            ->whereDate('scheduled_at', $targetDate)
            ->where('status', 'scheduled')
            ->where('is_notified', false)
            ->get();

        if ($appointments->isEmpty()) {
            $this->info("Aucun rappel à envoyer pour le : " . $targetDate);
            return;
        }

        $count = 0;
        foreach ($appointments as $rdv) {
            try {
                // Vérifier si la patiente existe
                if ($rdv->patient) {
                    // 3. Envoyer la Notification (Email + Cloche d'un coup !)
                    $rdv->patient->notify(new RappelCpnNotification($rdv));

                    // 4. Marquer comme notifié pour ne pas renvoyer le mail demain
                    $rdv->update(['is_notified' => true]);
                    
                    $count++;
                }
            } catch (\Exception $e) {
                Log::error("Erreur envoi rappel RDV #" . $rdv->id . " : " . $e->getMessage());
                $this->error("Erreur pour le RDV #" . $rdv->id);
            }
        }

        $this->info("Succès : $count rappels envoyés pour la date du $targetDate.");
    }
}