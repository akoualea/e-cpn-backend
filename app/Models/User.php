<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // Simplifié : Laravel utilise par défaut le schéma 'public' via le driver pgsql
    protected $table = 'profiles'; 
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; 

    protected $fillable = [
        'id', 
        'nom', 
        'prenom', 
        'email', 
        'role', 
        'google_id', 
        'matricule',
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    // RELATION AVEC MEDICAL_PROS (Indispensable pour le dashboard médecin)
    public function medicalPro()
    {
        // On précise bien 'id' comme clé car tu n'utilises pas user_id mais le même ID partout
        return $this->hasOne(MedicalPro::class, 'id', 'id');
    }

    // RELATION AVEC PATIENT (Indispensable pour le dashboard patient)
    public function patient()
    {
        return $this->hasOne(Patient::class, 'id', 'id');
    }

    // Fonctions JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        $specialite = null;
        if ($this->role === 'PRO' && $this->medicalPro) {
            $specialite = $this->medicalPro->specialite;
        }

        return [
            'role' => $this->role,
            'email' => $this->email,
            'specialite' => $specialite
        ];
    }
}