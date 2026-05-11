<?php
// --- POUR LA GESTION DES CPN ---
 namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $table = 'consultations';

    // AJOUT DES NOUVELLES COLONNES DES SLIDES DANS FILLABLE
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'cpn_number', 
        
        // --- DOSSIER OBSTÉTRICAL (Ce que tu avais déjà) ---
        'ddr', 
        'dpa', 
        'gs_rh', 
        'electrophorese_hb', 
        'gestite_g', 
        'parite_p', 
        'vivants_v',

        // --- CONSTANTES & SIGNES (Slide 10 & 13) ---
        'poids',
        'tension_arterielle',
        'oedemes',          // 🔴 AJOUTÉ (Slide 13)
        'ligne_brune',      // 🔴 AJOUTÉ (Slide 10)
        'vergetures',       // 🔴 AJOUTÉ (Slide 10)

        // --- EXAMEN FOETAL & UTÉRIN (Slide 10-14) ---
        'hauteur_uterine',
        'bcf',
        'presentation_foetus', // Céphalique, Siège, etc.
        'bassin',             // 🔴 AJOUTÉ (Slide 14)
        
        // --- TOUCHER VAGINAL (Slide 14) ---
        'col_position',     // 🔴 AJOUTÉ 
        'col_consistance',  // 🔴 AJOUTÉ
        'col_ouverture',    // 🔴 AJOUTÉ

        // --- TRAITEMENTS & EXAMENS (Slide 11-14) ---
        'tpi_paludisme',
        'fer_acide_folique',
        'resultat_bandelette', // 🔴 AJOUTÉ
        'test_osullivan',      // 🔴 AJOUTÉ
        'file_url',            // 🔴 AJOUTÉ (Pour l'Échographie/Analyse)

        // --- PRONOSTIC & PLAN (Slide 15) ---
        'pronostic',           // VOIE BASSE / VOIE HAUTE
        'lieu_accouchement_prevu', // 🔴 AJOUTÉ
        'accompagnateur_nom',      // 🔴 AJOUTÉ
        'observations'
    ];

    protected $casts = [
        'ddr' => 'date',
        'dpa' => 'date',
        'oedemes' => 'boolean',
        'ligne_brune' => 'boolean',
        'vergetures' => 'boolean',
        'tpi_paludisme' => 'boolean',
        'fer_acide_folique' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Relation : Une consultation appartient à une patiente
     */
    public function patient()
    {
        // On lie au modèle User (ou Patient selon ta structure)
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Relation : Une consultation appartient à un médecin
     */
    public function medecin()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}