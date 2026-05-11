<?php

// namespace App\Notifications;

// use Illuminate\Bus\Queueable;
// use Illuminate\Notifications\Notification;
// use App\Models\Appointment;

// class CPNReminder extends Notification
// {
//     use Queueable;

//     protected $appointment;

//     public function __construct(Appointment $appointment)
//     {
//         $this->appointment = $appointment;
//     }

    
//     public function via($notifiable)
//     {
//         return ['database'];
//     }

//     // Le contenu qui sera envoyé à React
//     public function toArray($notifiable)
//     {
//         return [
//             'type' => 'rappel_cpn',
//             'title' => 'Rappel de consultation',
//             'message' => "Mme, votre consultation n°" . $this->appointment->cpn_number . " est prévue dans 48h.",
//             'appointment_id' => $this->appointment->id,
//             'date' => $this->appointment->scheduled_at,
//         ];
//     }
// }