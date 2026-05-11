<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Journal extends Model
{
    // 1. On dit à Laravel d'utiliser ta table existante
    protected $table = 'journals';

    // 2. Ta base utilise des UUID, donc on dit à Laravel que ce n'est pas un chiffre (1, 2, 3...)
    protected $keyType = 'string';
    public $incrementing = false;

    // 3. Ta table a seulement 'created_at', pas de 'updated_at' selon ton dump.
    // On dit à Laravel de ne pas chercher 'updated_at'
    const UPDATED_AT = null;

    // 4. Les colonnes que React a le droit de remplir
    protected $fillable = [
        'id',
        'patient_id',
        'titre',
        'note',
        'humeur',
        'created_at'
    ];

    // 5. Petit script pour générer l'UUID automatiquement quand on crée une note
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}