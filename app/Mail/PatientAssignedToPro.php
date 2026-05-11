<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User; // Le modèle de patient est aussi un User pour le profil

class PatientAssignedToPro extends Mailable
{
    use Queueable, SerializesModels;

    public $patientUser;
    public $proUser;
    public $recipientType; // 'pro' ou 'patient'

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $patientUser, User $proUser, string $recipientType)
    {
        $this->patientUser = $patientUser;
        $this->proUser = $proUser;
        $this->recipientType = $recipientType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->recipientType === 'pro') {
            return $this->subject('Un nouveau patient vous a été attribué !')
                        ->markdown('emails.patients.assigned_to_pro_for_pro');
        } else { // recipientType === 'patient'
            return $this->subject('Votre médecin traitant a été désigné !')
                        ->markdown('emails.patients.assigned_to_pro_for_patient');
        }
    }
}