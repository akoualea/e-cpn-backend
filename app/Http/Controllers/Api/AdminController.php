<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth; // AJOUTÉ : Pour que le Auth::guard fonctionne
use App\Mail\ProAccountValidated; 
use App\Mail\PatientAssignedToPro; 

class AdminController extends Controller
{
    public function getPros() { 
        return response()->json(User::where('role', 'PRO')->with('medicalPro')->get()); 
    }
    
    public function getPendingPros() { 
        return response()->json(User::where('role', 'PRO')->whereHas('medicalPro', function($q) { $q->where('is_verified', false); })->with('medicalPro')->get()); 
    }
    
    public function getVerifiedPros() { 
        return response()->json(User::where('role', 'PRO')->whereHas('medicalPro', function($q) { $q->where('is_verified', true); })->with('medicalPro')->get()); 
    }
    
    public function getPatients() {
        return response()->json(DB::table('profiles')
            ->join('patients', 'profiles.id', '=', 'patients.id')
            ->where('profiles.role', 'PATIENT')
            ->select('profiles.*', 'patients.assigned_pro_id')
            ->get());
    }

    public function validatePro(Request $request, $id) {
        $user = User::findOrFail($id); 
        
        if ($request->action === 'approve') {
            DB::table('medical_pros')->where('id', $id)->update(['is_verified' => true]);
            Mail::to($user->email)->send(new ProAccountValidated($user));
            return response()->json(['message' => 'Médecin approuvé et email envoyé']);
        } else {
            DB::table('medical_pros')->where('id', $id)->delete();
            return response()->json(['message' => 'Médecin refusé']);
        }
    }

    /**
     * ASSIGNATION (Régle la visibilité de l'un vers l'autre)
     */
    public function assignPatient(Request $request) {
        $request->validate([
            'patient_id' => 'required',
            'pro_id' => 'required'
        ]);

        // Mise à jour de l'assignation
        DB::table('patients')
            ->where('id', $request->patient_id)
            ->update(['assigned_pro_id' => $request->pro_id]);

        $patientUser = User::find($request->patient_id);
        $proUser = User::find($request->pro_id);

        if ($patientUser && $proUser) {
            Mail::to($proUser->email)->send(new PatientAssignedToPro($patientUser, $proUser, 'pro'));
            Mail::to($patientUser->email)->send(new PatientAssignedToPro($patientUser, $proUser, 'patient'));
        }

        return response()->json(['message' => 'Assignation réussie']);
    }

    /**
     * FONCTION DE RÉCUPÉRATION DU PROFIL (INTEGRÉE ICI)
     * C'est elle qui envoie le nom du médecin à la patiente !
     */
    public function me()
{
    $user = Auth::guard('api')->user();
    if (!$user) return response()->json(['error' => 'Non autorisé'], 401);

    // Attention aux noms ici :
    // 'patient' : relation dans User.php
    // 'assignedPro' : relation dans Patient.php
    // 'profile' : relation dans MedicalPro.php (qui pointe vers ta table PROFILES)
    $profile = User::with([
        'medicalPro', 
        'patient.assignedPro.profile' 
    ])->find($user->id);

    return response()->json($profile);
}
}