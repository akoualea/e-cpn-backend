<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'patients'; // Assurez-vous que le nom de la table est correct

    public $incrementing = false; // Désactive l'incrémentation automatique
    protected $keyType = 'string'; // Type de la clé primaire

    protected $fillable = [
        'id',
        'patient_id',
        'somme_estimee',
        'epargne_actuelle',
        'decisionnaire',
        // Ajoutez les autres champs de votre table budgets ici
    ];

    public $timestamps = false;

    // Boot method pour auto-générer l'UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::uuid4()->toString();
        });
    }

    // Définir la relation avec le patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}