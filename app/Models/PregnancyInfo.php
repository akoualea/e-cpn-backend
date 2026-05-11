<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PregnancyInfo extends Model
{
    // On précise le nom de la table car elle a un "s" à la fin
    protected $table = 'pregnancy_infos';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'ddr_date',
        'date_accouchement_prevue',
        'current_cpn',
        'is_active'
    ];
}