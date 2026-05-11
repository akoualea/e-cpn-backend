<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    


    protected function schedule(Schedule $schedule): void
    {
        // Puisque tu as déjà créé la commande 'cpn:send-reminders' 
        // dans ton fichier RemindCPN.php, utilise-la simplement ici :
        
        $schedule->command('cpn:send-reminders')->dailyAt('07:00');
        
        // C'est beaucoup plus propre ! Laravel ira lire la logique 
        // que tu as déjà écrite dans ton fichier de commande.
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}