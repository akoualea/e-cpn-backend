<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MedicalPro;
use App\Models\User; 
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProAccountValidated;
use App\Mail\PatientAssignedToPro;
use Illuminate\Support\Facades\Log;

class MedicalProController extends Controller
{
    // Récupérer tous les professionnels de santé
    public function index()
    {
        $medicalPros = User::where('role', 'PRO')->with('medicalPro')->get();
        return response()->json($medicalPros);
    }

    // Récupérer les pros en attente
    public function pendingPros()
    {
        $pendingPros = User::where('role', 'PRO')
                           ->whereHas('medicalPro', function ($query) {
                               $query->where('is_verified', false);
                           })
                           ->with('medicalPro')
                           ->get();
        return response()->json($pendingPros);
    }

    // Récupérer les pros validés
    public function verifiedPros()
    {
        $verifiedPros = User::where('role', 'PRO')
                           ->whereHas('medicalPro', function ($query) {
                               $query->where('is_verified', true);
                           })
                           ->with('medicalPro')
                           ->get();
        return response()->json($verifiedPros);
    }

    // --- NOUVELLE FONCTION : Liste des patientes du médecin connecté ---
   // app/Http/Controllers/Api/MedicalProController.php

public function myPatients()
{
    try {
        $doctorId = auth()->id(); 

        $patients = DB::table('patients')
            ->join('profiles', 'patients.id', '=', 'profiles.id')
            // ✅ On joint la table pregnancy_infos pour savoir si le suivi existe
            ->leftJoin('pregnancy_infos', function($join) {
                $join->on('patients.id', '=', 'pregnancy_infos.patient_id')
                     // On ne prend que le suivi qui n'a pas été archivé
                     ->where('pregnancy_infos.is_active', '=', true);
            })
            ->where('patients.assigned_pro_id', $doctorId)
            ->select([
                'profiles.nom', 
                'profiles.prenom', 
                'profiles.email', 
                'patients.id', 
                'patients.epargne_actuelle',
                // ✅ TRÈS IMPORTANT : On envoie la date DDR sous le nom 'suivi_active'
                // Si cette colonne est remplie, le formulaire disparaîtra dans React
                'pregnancy_infos.ddr_date as suivi_active' 
            ])
            ->get();

        return response()->json($patients);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur SQL : ' . $e->getMessage()], 500);
    }
}

    // Valider ou refuser un pro
    public function validatePro(Request $request, $id)
    {
        $request->validate(['action' => 'required|in:approve,reject']);

        try {
            DB::beginTransaction();
            $medicalPro = MedicalPro::where('id', $id)->firstOrFail();
            $user = User::where('id', $id)->firstOrFail();

            if ($request->action === 'approve') {
                $medicalPro->is_verified = true;
                $medicalPro->save();
                Mail::to($user->email)->send(new ProAccountValidated($user));
                DB::commit();
                return response()->json(['message' => 'Professionnel de santé validé.']);
            } else {
                $user->delete(); 
                $medicalPro->delete(); 
                DB::commit();
                return response()->json(['message' => 'Professionnel refusé.']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Attribuer un patient (utilisé par l'Admin)
    public function assignPatientToPro(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|string|exists:patients,id',
            'pro_id' => 'required|string|exists:medical_pros,id',
        ]);

        try {
            DB::beginTransaction();

            $patient = Patient::where('id', $request->patient_id)->firstOrFail();
            $proUser = User::where('id', $request->pro_id)->firstOrFail();
            $patientUser = User::where('id', $request->patient_id)->firstOrFail();

            $patient->assigned_pro_id = $request->pro_id;
            $patient->save();

            Mail::to($proUser->email)->send(new PatientAssignedToPro($patientUser, $proUser, 'pro'));
            Mail::to($patientUser->email)->send(new PatientAssignedToPro($patientUser, $proUser, 'patient'));

            DB::commit();
            return response()->json(['message' => 'Assignation réussie.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


     public function getCPNHierParJour()
 {
   $doctorId = auth()->id();
   $today = Carbon::today();

// 1. Compter tous les CPN planifiés pour le médecin pour aujourd'hui
 $totalCpn = DB::table('appointments')
          ->where('doctor_id', $doctorId)
          ->where('type', 'CPN')
          ->whereDate('scheduled_at', $today)
          ->count();
    
// 2. Idéalement tu aurais aussi :
// Compter les CPN validés, pour faire un décompte (Ex: 2 / 5)

     return response()->json(['cpnTotalAujourdhui' => $totalCpn]);
 }

public function getCPNTotalAujourdhui()
{
    $doctorId = auth()->id();
    $today = \Carbon\Carbon::today();

    // 1. CPN déjà terminés aujourd'hui
    $done = DB::table('appointments')
             ->where('doctor_id', $doctorId)
             ->where('type', 'CPN')
             ->where('status', 'completed')
             ->whereDate('scheduled_at', $today)
             ->count();

    // 2. CPN qui restent à faire aujourd'hui (statut 'scheduled' ou 'pending')
    $remaining = DB::table('appointments')
                  ->where('doctor_id', $doctorId)
                  ->where('type', 'CPN')
                  ->where('status', 'scheduled')
                  ->whereDate('scheduled_at', $today)
                  ->count();

    return response()->json([
        'done' => $done,
        'remaining' => $remaining
    ]);
}
}