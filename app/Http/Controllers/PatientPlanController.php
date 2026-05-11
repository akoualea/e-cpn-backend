<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;

class PatientPlanController extends Controller
{
    public function getPlan($id) {
        // On récupère directement le patient par son ID (UUID)
        $patient = Patient::findOrFail($id);
        return response()->json($patient);
    }

    public function updatePlan(Request $request, $id) {
        $patient = Patient::findOrFail($id);

        // Calcul automatique du Budget Total Estimé
        $somme_estimee = 
            (float)$request->input('prix_trousseau_mere', $patient->prix_trousseau_mere) + 
            (float)$request->input('prix_trousseau_bebe', $patient->prix_trousseau_bebe) + 
            (float)$request->input('prix_examens', $patient->prix_examens) + 
            (float)$request->input('prix_imprevus', $patient->prix_imprevus) + 
            (float)$request->input('prix_cesarienne', $patient->prix_cesarienne);

        // Mise à jour des champs
        $patient->fill($request->all());
        $patient->somme_estimee = $somme_estimee;
        
        // Recalcul de l'épargne actuelle totale
        $patient->epargne_actuelle = 
            (float)$patient->epargne_trousseau_mere + 
            (float)$patient->epargne_trousseau_bebe + 
            (float)$patient->epargne_examens + 
            (float)$patient->epargne_imprevus + 
            (float)$patient->epargne_cesarienne;

        $patient->save();

        return response()->json($patient);
    }

    public function deposit(Request $request, $id) {
        $patient = Patient::findOrFail($id);
        $category = $request->input('category'); 
        $amount = (float)$request->input('amount');

        // Liste des catégories autorisées
        $categories = ['epargne_trousseau_mere', 'epargne_trousseau_bebe', 'epargne_examens', 'epargne_imprevus', 'epargne_cesarienne'];

        if (in_array($category, $categories)) {
            $patient->increment($category, $amount);
            $patient->increment('epargne_actuelle', $amount);
        }

        return response()->json(['message' => 'Dépôt réussi', 'patient' => $patient->fresh()]);
    }
}