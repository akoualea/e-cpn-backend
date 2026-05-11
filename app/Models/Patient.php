<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'id';
    protected $keyType = 'string'; 
    public $incrementing = false; 

    protected $fillable = [
    'id', 
    'assigned_pro_id',
    'lieu_prevu', 
    'transport_type', 
    'accompagnateur_nom', 
    'accompagnateur_contact',
    'decisionnaire',      
    'donneur_nom',        
    'donneur_contact',    
    'prix_trousseau_mere', 
    'prix_trousseau_bebe', 
    'prix_examens',        
    'prix_imprevus',       
    'prix_cesarienne',     
    'epargne_actuelle', 
    'somme_estimee'
];
    public $timestamps = false;

    // RELATION : Permet de trouver le médecin assigné
    public function assignedPro()
    {
        return $this->belongsTo(MedicalPro::class, 'assigned_pro_id', 'id');
    }

 
public function pregnancy_infos()
{
    // Un patient peut avoir plusieurs suivis (historique)
    // On lie le 'patient_id' de la table pregnancy_infos à l'id du patient
    return $this->hasMany(PregnancyInfo::class, 'patient_id', 'id');
}
}