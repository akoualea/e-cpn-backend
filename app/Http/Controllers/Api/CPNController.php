<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CpnProtocol;


class CPNController extends Controller
{
    public function initialiserSuivi(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|uuid',
            'ddr_date' => 'required|date',
        ]);

        $alreadyHasProtocol = DB::table('cpn_protocol')
            ->where('patient_id', $request->patient_id)
            ->exists();

        if ($alreadyHasProtocol) {
            return response()->json([
                'status' => 'conflict',
                'message' => '🚫 ACTION REFUSÉE : Un calendrier de 8 CPN est déjà actif pour cette patiente.'
            ], 422);
        }

        return DB::transaction(function () use ($request) {
            try {
                $ddr = Carbon::parse($request->ddr_date);
                $dpa = $ddr->copy()->addWeeks(40);

                DB::table('pregnancy_infos')->updateOrInsert(
                    ['patient_id' => $request->patient_id],
                    [
                        'doctor_id' => auth()->id(),
                        'ddr_date' => $request->ddr_date,
                        'date_accouchement_prevue' => $dpa->toDateString(),
                        'current_cpn' => 0,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                $semainesOms = [12, 20, 26, 30, 34, 36, 38, 40];
                $dataToInsert = [];
                foreach ($semainesOms as $index => $semaine) {
                    $dataToInsert[] = [
                        'patient_id' => $request->patient_id,
                        'cpn_number' => $index + 1,
                        'date_theorique' => $ddr->copy()->addWeeks($semaine)->toDateString(),
                        'status' => 'scheduled',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                DB::table('cpn_protocol')->insert($dataToInsert);

                return response()->json(['message' => 'Le protocole des 8 CPN a été généré avec succès.']);
            } catch (\Exception $e) {
                Log::error("Erreur génération CPN : " . $e->getMessage());
                return response()->json(['message' => 'Erreur technique'], 500);
            }
        });
    }

    public function getMonSuivi(Request $request)
{
    // Si le médecin appelle la route, il passe ?patient_id=...
    // Si la patiente l'appelle, on prend son ID via son token (auth()->id())
    $patientId = $request->query('patient_id') ?? auth()->id();

    try {
        $suivi = DB::table('cpn_protocol')
            ->leftJoin('consultations', function ($join) {
                $join->on('cpn_protocol.patient_id', '=', 'consultations.patient_id')
                     ->on('cpn_protocol.cpn_number', '=', 'consultations.cpn_number');
            })
            ->where('cpn_protocol.patient_id', $patientId)
            ->select([
                'cpn_protocol.id',
                'cpn_protocol.cpn_number',
                'cpn_protocol.date_theorique',
                'cpn_protocol.status',
                
                // Champs de base
                'consultations.poids',
                'consultations.tension_arterielle',
                'consultations.hauteur_uterine',
                'consultations.bcf',
                'consultations.observations',
                'consultations.file_url',

                // ✅ AJOUTE CES CHAMPS POUR LA PATIENTE AUSSI :
                'consultations.gs_rh',
                'consultations.electrophorese_hb',
                'consultations.gestite_g',
                'consultations.parite_p',
                'consultations.presentation_foetus',
                'consultations.bassin',
                'consultations.col_position',
                'consultations.col_ouverture',
                'consultations.pronostic'
            ])
            ->orderBy('cpn_protocol.cpn_number', 'asc')
            ->get();

        return response()->json($suivi);

    } catch (\Exception $e) {
        Log::error("Erreur SQL getMonSuivi : " . $e->getMessage());
        return response()->json(['error' => 'Erreur de chargement'], 500);
    }
}

    public function enregistrerActe(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|uuid',
            'cpn_number' => 'required|integer',
            'poids' => 'required',
            'tension_arterielle' => 'required',
            'hauteur_uterine' => 'nullable',
            'bcf' => 'nullable',
            'observations' => 'nullable',
            'gs_rh' => 'nullable',
            'electrophorese_hb' => 'nullable',
            'gestite_g' => 'nullable',
            'parite_p' => 'nullable',
            'vivants_v' => 'nullable',
            'oedemes' => 'boolean',
            'tpi_paludisme' => 'boolean',
            'fer_acide_folique' => 'boolean',
            'resultat_bandelette' => 'nullable',
            'test_osullivan' => 'nullable',
            'presentation_foetus' => 'nullable',
            'bassin' => 'nullable',
            'col_position' => 'nullable',
            'col_consistance' => 'nullable',
            'col_ouverture' => 'nullable',
            'pronostic' => 'nullable',
            'lieu_accouchement_prevu' => 'nullable',
            'accompagnateur_nom' => 'nullable',
            'file_url' => 'nullable'
        ]);

        try {
            return DB::transaction(function () use ($data) {
                \App\Models\Consultation::create(array_merge($data, [
                    'doctor_id' => auth()->id()
                ]));

                DB::table('cpn_protocol')
                    ->where('patient_id', $data['patient_id'])
                    ->where('cpn_number', $data['cpn_number'])
                    ->update(['status' => 'completed', 'updated_at' => now()]);

                return response()->json(['message' => 'Consultation validée !'], 200);
            });
        } catch (\Exception $e) {
            Log::error("Erreur enregistrerActe : " . $e->getMessage());
            return response()->json(['message' => 'Erreur sauvegarde'], 500);
        }
    }

  public function downloadPDF($id)
{
    try {
        // 1. Récupération de la ligne correspondante dans le protocole
        $protocol = DB::table('cpn_protocol')->where('id', $id)->first();
        
        if (!$protocol) {
            return response()->json(['error' => 'Protocole de suivi introuvable.'], 404);
        }

        // 2. RÉSOLUTION DU BUG D'IMAGE : On utilise ->latest() 
        // On récupère la toute dernière consultation enregistrée pour ce patient et ce numéro CPN
        $consultation = \App\Models\Consultation::where('patient_id', $protocol->patient_id)
            ->where('cpn_number', $protocol->cpn_number)
            ->latest('created_at') 
            ->first();

        if (!$consultation) {
            return response()->json(['error' => 'Aucune donnée médicale trouvée pour cette consultation.'], 404);
        }

        // 3. ENREGISTREMENT DE LA NOTIFICATION (Pour la cloche de la maman)
        DB::table('notifications')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\RapportTelecharge',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $protocol->patient_id,
            'data' => json_encode([
                'title' => 'DOCUMENTS PRÊTS',
                'message' => "Le rapport officiel de votre CPN n°" . $protocol->cpn_number . " a été téléchargé.",
                'type' => 'info' // Couleur bleue dans le dashboard
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. LOGIQUE IMAGE EXAMEN (Conversion Base64 pour affichage PDF)
        $imageData = null;
        if (!empty($consultation->file_url)) {
            try {
                // On utilise un contexte SSL pour éviter les blocages de sécurité sur certains réseaux
                $context = stream_context_create(["ssl" => ["verify_peer" => false, "verify_peer_name" => false]]);
                $content = @file_get_contents($consultation->file_url, false, $context);
                if ($content) {
                    $imageData = 'data:image/jpeg;base64,' . base64_encode($content);
                }
            } catch (\Exception $e) { 
                \Log::error("Erreur récupération image examen: " . $e->getMessage()); 
            }
        }

        // 5. LOGIQUE SIGNATURE (Image stockée dans public/assets/signature.png)
        $signatureData = null;
        $signPath = public_path('assets/signature.png');
        if (file_exists($signPath)) {
            $signatureData = 'data:image/png;base64,' . base64_encode(file_get_contents($signPath));
        }

        // 6. CONSTRUCTION DE L'OBJET FINAL POUR LE PDF
        $c = (object) [
            'id'                 => $consultation->id,
            'cpn_number'         => $consultation->cpn_number,
            'patient_nom'        => $consultation->patient->nom ?? 'Patiente',
            'patient_prenom'     => $consultation->patient->prenom ?? '',
            'doctor_nom'         => $consultation->medecin->nom ?? 'Praticien',
            'doctor_prenom'      => $consultation->medecin->prenom ?? '',
            'poids'              => $consultation->poids,
            'tension_arterielle' => $consultation->tension_arterielle,
            'hauteur_uterine'    => $consultation->hauteur_uterine,
            'bcf'                => $consultation->bcf,
            'gs_rh'              => $consultation->gs_rh,
            'electrophorese_hb'  => $consultation->electrophorese_hb,
            'gestite_g'          => $consultation->gestite_g,
            'parite_p'           => $consultation->parite_p,
            'presentation_foetus'=> $consultation->presentation_foetus,
            'bassin'             => $consultation->bassin,
            'col_position'       => $consultation->col_position,
            'col_ouverture'      => $consultation->col_ouverture,
            'pronostic'          => $consultation->pronostic,
            'observations'       => $consultation->observations,
            'file_url'           => $consultation->file_url,
            'created_at'         => $consultation->created_at,
        ];

        // 7. GÉNÉRATION VIA LA VUE BLADE
        $pdf = Pdf::loadView('pdf.consultation_report', [
            'c' => $c,
            'date' => date('d/m/Y à H:i'),
            'image' => $imageData,
            'signature' => $signatureData
        ]);

        // Nom du fichier : Rapport_CPN1_NOMPATIENTE.pdf
        $fileName = 'Rapport_CPN' . $protocol->cpn_number . '_' . ($consultation->patient->nom ?? 'DOC') . '.pdf';
        
        return $pdf->download($fileName);

    } catch (\Exception $e) {
        \Log::error("Échec critique PDF : " . $e->getMessage());
        return response()->json(['error' => 'Erreur technique : impossible de générer le rapport.'], 500);
    }
}

public function getConsultationsCount($patientId)
    {
        // On compte seulement les CPN qui ont le statut "completed"
        $count = DB::table('cpn_protocol')
            ->where('patient_id', $patientId)
            ->where('status', 'completed')
            ->count();

        // Nombre total de consultations prévues (8)
        $total = 8;

        return response()->json(['count' => $count, 'total' => $total]);
    }
}