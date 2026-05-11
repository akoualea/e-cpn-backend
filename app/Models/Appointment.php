<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id', 
        'doctor_id', 
        'scheduled_at', 
        'type', 
        'cpn_number', 
        'status', 
        'is_online', 
        'reason', 
        'is_notified'
    ];

    /**
     * Relation avec l'utilisateur (Patiente).
     */
    public function patient(): BelongsTo
    {
        // On lie le patient_id du RDV à l'ID de la table users
        return $this->belongsTo(User::class, 'patient_id', 'id');
    }
}