<?php

namespace App\Http\Controllers\Api;
  use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Appointment;
use App\Http\Controllers\Controller;
use App\Models\PregnancyInfo;
use App\Models\Consultation; 
use App\Models\User; 
use App\Notifications\RappelCpnNotification; 
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification; 

class ConsultationController extends Controller
{
    /**
     * DÉCLENCHEUR : Génère les 8 CPN dès la DDR saisie
     */
//     public function demarrerSuivi(Request $request)
//     {
//         $request->validate([
//             'patient_id' => 'required|uuid',
//             'ddr_date' => 'required|date',
//         ]);

//         $ddr = Carbon::parse($request->ddr_date);
//         $dpa = $ddr->copy()->addDays(280);
//         $doctorId = auth()->id();

//         try {
//             DB::beginTransaction();

//             $semainesOMS = [12, 20, 26, 30, 34, 36, 38, 40];

//             foreach ($semainesOMS as $index => $semaine) {
//                 Appointment::create([
//                     'patient_id' => $request->patient_id,
//                     'doctor_id' => $doctorId,
//                     'scheduled_at' => $ddr->copy()->addWeeks($semaine)->setHour(8),
//                     'type' => 'CPN',
//                     'cpn_number' => $index + 1,
//                     'status' => 'scheduled',
//                     'reason' => "Consultation Prénatale n°" . ($index + 1)
//                 ]);
//             }

//             PregnancyInfo::updateOrCreate(
//                 ['patient_id' => $request->patient_id],
//                 ['doctor_id' => $doctorId, 'ddr_date' => $ddr, 'date_accouchement_prevue' => $dpa]
//             );

//             DB::commit();

//             // ENVOI DU MAIL DE BIENVENUE / PROTOCOLE ACTIVÉ
//             $patient = User::find($request->patient_id);
//             if ($patient) {
//                 Notification::send($patient, new RappelCpnNotification(Appointment::where('patient_id', $patient->id)->first()));
//             }

//             return response()->json(['message' => 'Protocole 8 CPN activé. DPA: ' . $dpa->format('d/m/Y')]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json(['error' => $e->getMessage()], 500);
//         }
//     }

// /**
//      * ENREGISTREMENT : Sauvegarde les données des Slides 10 à 15
//      * Et met à jour le protocole CPN
//      */
//     public function enregistrerActe(Request $request)
// {
//     $data = $request->validate([
//         'patient_id' => 'required|uuid',
//         'cpn_number' => 'required|integer',
//         'poids'      => 'required',
//         'tension_arterielle' => 'required',
//         'hauteur_uterine'    => 'nullable',
//         'bcf'                => 'nullable',
//         'observations'       => 'nullable|string',
//         'file_url'           => 'nullable|string', // Reçu depuis Supabase Storage
//         // Champs CPN 1
//         'gs_rh' => 'nullable|string',
//         'electrophorese_hb' => 'nullable|string',
//         'gestite_g' => 'nullable|integer',
//         'parite_p'  => 'nullable|integer'
//     ]);

//     try {
//         return DB::transaction(function () use ($data) {
            
//             // 1. Sauvegarder dans la table CONSULTATIONS
//             DB::table('consultations')->insert([
//                 'id' => \Str::uuid(),
//                 'patient_id' => $data['patient_id'],
//                 'doctor_id'  => auth()->id(),
//                 'cpn_number' => $data['cpn_number'],
//                 'poids'      => $data['poids'],
//                 'tension_arterielle' => $data['tension_arterielle'],
//                 'hauteur_uterine'    => $data['hauteur_uterine'],
//                 'bcf'                => $data['bcf'],
//                 'observations'       => $data['observations'],
//                 'gs_rh'              => $data['gs_rh'] ?? null,
//                 'electrophorese_hb'  => $data['electrophorese_hb'] ?? null,
//                 'gestite_g'          => $data['gestite_g'] ?? null,
//                 'parite_p'           => $data['parite_p'] ?? null,
//                 'file_url'           => $data['file_url'] ?? null,
//                 'created_at'         => now()
//             ]);

//             // 2. ARCHIVER DANS LA TABLE EXAMS (Si un fichier est joint)
//             if (!empty($data['file_url'])) {
//                 DB::table('exams')->insert([
//                     'id'          => \Str::uuid(),
//                     'patient_id'  => $data['patient_id'],
//                     'pro_id'      => auth()->id(),
//                     'titre'       => "Examen CPN n°" . $data['cpn_number'],
//                     'type_examen' => ($data['cpn_number'] == 1) ? 'Biologie' : 'Imagerie',
//                     'fichier_url' => $data['file_url'],
//                     'created_at'  => now()
//                 ]);
//             }

//             // 3. PASSER LA CARTE CPN AU VERT
//             DB::table('cpn_protocol')
//                 ->where('patient_id', $data['patient_id'])
//                 ->where('cpn_number', $data['cpn_number'])
//                 ->update(['status' => 'completed', 'updated_at' => now()]);

//             // 4. Mettre à jour le suivi global
//             DB::table('pregnancy_infos')
//                 ->where('patient_id', $data['patient_id'])
//                 ->update(['current_cpn' => $data['cpn_number'], 'updated_at' => now()]);

//             return response()->json(['message' => 'Consultation et Examen validés !']);
//         });
//     } catch (\Exception $e) {
//         \Log::error("Erreur save-consultation: " . $e->getMessage());
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }


   public function demanderUrgence(Request $request)
{
    $request->validate([
        'doctor_id' => 'required|uuid',
        'reason' => 'required|string'
    ]);

    try {
        $patientId = auth()->id();
        $doctorId = $request->doctor_id;
        $reason = $request->reason;

        // ✅ 1. SÉCURITÉ ANTI-FANTÔME : On vérifie si une urgence est déjà en attente
        // On utilise DB::table car c'est plus rapide que de faire un appel HTTP à Supabase pour vérifier
        $exists = DB::table('appointments')
            ->where('patient_id', $patientId)
            ->where('type', 'Urgence')
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '🚨 Vous avez déjà une demande d\'urgence en attente. Votre médecin a déjà été alerté.'
            ], 422);
        }

        // Log des données (Tu aimes garder tes logs, c'est bien !)
        Log::info('Tentative envoi Urgence Supabase...', ['patient' => $patientId]);

        // 2. Envoi vers Supabase (On garde ta méthode Http)
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_SERVICE'),
            'Authorization' => 'Bearer ' . env('SUPABASE_SERVICE'),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=minimal'
        ])->post(env('SUPABASE_URL') . '/rest/v1/appointments', [
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'scheduled_at' => now()->toDateTimeString(),
            'type' => 'Urgence',
            'reason' => $reason,
            'status' => 'pending',
            'is_online' => true
        ]);

        if (!$response->successful()) {
            throw new \Exception("Erreur Supabase : " . $response->body());
        }

        return response()->json([
            'success' => true,
            'message' => '🚨 Votre urgence a été signalée au Dr. ! Restez connectée.'
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur demande urgence: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Impossible de signaler l\'urgence pour le moment.'
        ], 500);
    }
}

    /**
     * RÉPONDRE À L'URGENCE : Le médecin convoque la patiente
     */
    public function repondreUrgence(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required',
            'patient_id' => 'required',
            'message' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            // A. On passe le rendez-vous en "in_progress" (pour le radar médecin)
            $appointment = Appointment::find($request->appointment_id);
            if ($appointment) {
                $appointment->update(['status' => 'in_progress']);
            }

            // B. On crée la notification pour la cloche de la maman
            // On utilise json_encode pour que la colonne data soit lisible par le frontend React
            DB::table('notifications')->insert([
                'id' => Str::uuid(),
                'type' => 'App\Notifications\UrgenceReponse',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $request->patient_id,
                'data' => json_encode([
                    'title' => 'CONVOCATION URGENCE',
                    'message' => $request->message,
                    'type' => 'urgence' // Crucial pour le design ROSE dans la NotificationBell
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Patiente convoquée et notifiée avec succès']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }public function getNextCpn($patientId)
{
    // On cherche le premier rendez-vous qui n'est pas encore "completed"
    $nextCpn = \DB::table('cpn_protocol')
        ->where('patient_id', $patientId)
        ->where('status', 'scheduled') // Vérifie bien que c'est écrit 'scheduled' en base
        ->orderBy('cpn_number', 'asc')
        ->first();

    if ($nextCpn) {
        return response()->json([
            'exists' => true,
            'date' => $nextCpn->date_theorique, // Vérifie que ta colonne s'appelle bien date_theorique
            'number' => $nextCpn->cpn_number
        ]);
    }

    return response()->json(['exists' => false]);
}

    /**
     * STATS : Compteur pour le dashboard médecin
     */
    public function getStats()
    {
        $doctorId = auth()->id();

        $urgenciesCount = Appointment::where('doctor_id', $doctorId)
            ->where('type', 'Urgence')
            ->where('status', 'pending')
            ->count();
        
        return response()->json([
            'urgencies' => $urgenciesCount
        ]);
    }


    public function count(Request $request, $patientId)
    {
        try {
            $count = Consultation::where('patient_id', $patientId)->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error("Erreur dans ConsultationController::count: " . $e->getMessage());
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
}

// -------------------------------------------------------------------------
// ANCIENS CODES COMMENTÉS TELS QUELS
// -------------------------------------------------------------------------

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Appointment;
// use App\Models\PregnancyInfo;
// use App\Models\Consultation; 
// use Carbon\Carbon;
// use Illuminate\Support\Facades\DB;

// class ConsultationController extends Controller
// {
//     /**
//      * 1. INITIALISATION (DDR -> Génération des 8 CPN)
//      */
//     public function demarrerSuivi(Request $request)
//     {
//         $request->validate([
//             'patient_id' => 'required|uuid',
//             'ddr_date' => 'required|date',
//         ]);

//         $ddr = Carbon::parse($request->ddr_date);
//         $doctorId = auth()->id();

//         // Calcul de la Date Prévue d'Accouchement (DPA) : DDR + 280 jours
//         $dpa = $ddr->copy()->addDays(280);

//         try {
//             DB::beginTransaction();

//             $semainesOMS = [12, 20, 26, 30, 34, 36, 38, 40];

//             foreach ($semainesOMS as $index => $semaine) {
//                 Appointment::create([
//                     'patient_id' => $request->patient_id,
//                     'doctor_id' => $doctorId,
//                     'scheduled_at' => $ddr->copy()->addWeeks($semaine)->setHour(8),
//                     'type' => 'CPN',
//                     'cpn_number' => $index + 1,
//                     'status' => 'scheduled',
//                     'is_online' => true,
//                     'reason' => "Consultation Prénatale n°" . ($index + 1)
//                 ]);
//             }

//             PregnancyInfo::updateOrCreate(
//                 ['patient_id' => $request->patient_id],
//                 [
//                     'doctor_id' => $doctorId,
//                     'ddr_date' => $ddr,
//                     'date_accouchement_prevue' => $dpa
//                 ]
//             );

//             DB::commit();
//             return response()->json(['message' => 'Protocole OMS activé. DPA prévue le : ' . $dpa->format('d/m/Y')]);

//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json(['error' => $e->getMessage()], 500);
//         }
//     }

//     /**
//      * 2. ENREGISTREMENT DE L'ACTE MÉDICAL
//      */
//     public function enregistrerActe(Request $request)
//     {
//         $request->validate([
//             'patient_id' => 'required|uuid',
//             'cpn_number' => 'required|integer',
//             'poids' => 'required|numeric',
//             'tension_arterielle' => 'required|string',
//         ]);

//         try {
//             $consultation = Consultation::create(array_merge(
//                 $request->all(),
//                 ['doctor_id' => auth()->id()]
//             ));

//             Appointment::where('patient_id', $request->patient_id)
//                 ->where('cpn_number', $request->cpn_number)
//                 ->update(['status' => 'completed']);

//             return response()->json([
//                 'message' => 'Acte médical enregistré avec succès',
//                 'data' => $consultation
//             ]);

//         } catch (\Exception $e) {
//             return response()->json(['error' => $e->getMessage()], 500);
//         }
//     }

//     /**
//      * 3. DEMANDE D'URGENCE (MAMAN)
//      */
//     public function demanderUrgence(Request $request)
//     {
//         $request->validate([
//             'doctor_id' => 'required',
//             'reason' => 'required',
//         ]);

//         $patientId = auth()->id();
//         $doctorId = $request->doctor_id;
//         $reason = $request->reason;

//         // 1. Création MySQL
//         try {
//             $appointment = Appointment::create([
//                 'patient_id' => $patientId,
//                 'doctor_id' => $doctorId,
//                 'scheduled_at' => now(),
//                 'type' => 'Urgence',
//                 'reason' => $reason,
//                 'status' => 'pending',
//                 'is_online' => true,
//             ]);
//         } catch (\Exception $e) {
//             return response()->json(['error' => 'Erreur création urgence: ' . $e->getMessage()], 500);
//         }

//         // 2. Pousser vers Supabase
//         try {
//             \Illuminate\Support\Facades\Http::withHeaders([
//                 'apikey' => env('SUPABASE_KEY'),
//                 'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
//                 'Content-Type' => 'application/json',
//                 'Prefer' => 'return=minimal' 
//             ])->post(env('SUPABASE_URL') . '/rest/v1/appointments', [
//                 'patient_id' => $patientId,
//                 'doctor_id' => $doctorId,
//                 'scheduled_at' => now()->toDateTimeString(),
//                 'type' => 'Urgence',
//                 'reason' => $reason,
//                 'status' => 'pending',
//                 'is_online' => true
//             ]);
//         } catch (\Exception $e) {
//             \Log::error("Erreur push Supabase : " . $e->getMessage());
//         }

//         return response()->json([
//             'message' => 'Demande envoyée et radar mis à jour',
//             'data' => $appointment
//         ]);
//     }

//     /**
//      * 4. STATISTIQUES (RADAR)
//      */
//     public function getStats()
//     {
//         $doctorId = auth()->id();

//         $urgenciesCount = \App\Models\Appointment::where('doctor_id', $doctorId)
//             ->where('type', 'Urgence')
//             ->where('status', 'pending')
//             ->count();
        
//         return response()->json([
//             'urgencies' => $urgenciesCount
//         ]);
//     }

//     /**
//      * 5. REPONDRE A UNE URGENCE (MEDECIN) - UNE SEULE FOIS !
//      */
//     public function repondreUrgence(Request $request)
//     {
//         $request->validate([
//             'appointment_id' => 'required',
//             'patient_id' => 'required',
//             'message' => 'required|string'
//         ]);

//         try {
//             DB::beginTransaction();

//             // 1. Mettre à jour le statut
//             $appointment = Appointment::find($request->appointment_id);
//             if ($appointment) {
//                 $appointment->update(['status' => 'in_progress']);
//             }

//             // 2. Créer la notification pour la cloche
//             DB::table('notifications')->insert([
//                 'id' => \Illuminate\Support\Str::uuid(),
//                 'type' => 'App\Notifications\UrgenceReponse',
//                 'notifiable_type' => 'App\Models\User', // Assure-toi que ce chemin correspond à ton modèle User
//                 'notifiable_id' => $request->patient_id,
//                 'data' => json_encode([
//                     'title' => 'Urgence Acceptée',
//                     'message' => $request->message,
//                     'type' => 'urgence'
//                 ]),
//                 'created_at' => now(),
//                 'updated_at' => now(),
//             ]);

//             DB::commit();
//             return response()->json(['message' => 'Notification envoyée à la patiente']);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return response()->json(['error' => $e->getMessage()], 500);
//         }
//     }
// }