<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CpnProtocol extends Model
{
    protected $table = 'cpn_protocol';

    protected $fillable = [
        'patient_id',
        'cpn_number',
        'date_theorique',
        'status'
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}