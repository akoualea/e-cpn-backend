<?php

use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\CPNController;
use App\Http\Controllers\Api\MedicalProController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PatientPlanController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;         // 🔴 AJOUTÉ POUR LES NOTIFICATIONS
use Illuminate\Support\Facades\DB;   // 🔴 AJOUTÉ POUR LES NOTIFICATIONS

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES
|--------------------------------------------------------------------------
*/
Route::get('/consultations/{id}/pdf', [App\Http\Controllers\Api\CPNController::class, 'downloadPDF']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);
Route::post('/auth/register', [AuthController::class, 'register']);

// RÉCUPÉRATION DE COMPTE
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

// PLAN DE NAISSANCE
Route::get('/patients/{id}/plan', [PatientPlanController::class, 'getPlan']);
Route::post('/patients/{id}/plan/update', [PatientPlanController::class, 'updatePlan']);
Route::post('/patients/{id}/plan/deposit', [PatientPlanController::class, 'deposit']);

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES (JWT)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {
      
    Route::get('/me', [AuthController::class, 'me']);
   
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- PATIENTES (JOURNAL & SUIVI) ---
    // Ajoute cette ligne dans le groupe de routes protégées (middleware auth:sanctum)
Route::get('/patients/{patientId}/next-cpn', [App\Http\Controllers\Api\ConsultationController::class, 'getNextCpn']);
    Route::get('/patients/{patientId}/journals', [JournalController::class, 'index']);
    Route::post('/journals', [JournalController::class, 'store']);
    Route::put('/journals/{id}', [JournalController::class, 'update']);
    Route::delete('/journals/{id}', [JournalController::class, 'destroy']);
    Route::get('/patient/mon-suivi-cpn', [CPNController::class, 'getMonSuivi']);
    Route::get('/patients/{patientId}/consultations/count', [CPNController::class, 'getConsultationsCount']);
    


    // --- DASHBOARD ADMIN ---
    Route::get('/medical-pros', [AdminController::class, 'getPros']);
    Route::get('/medical-pros/pending', [AdminController::class, 'getPendingPros']);
    Route::get('/medical-pros/verified', [AdminController::class, 'getVerifiedPros']);
    Route::get('/patients', [AdminController::class, 'getPatients']);
    Route::post('/medical-pros/{id}/validate', [AdminController::class, 'validatePro']);
    Route::post('/medical-pros/assign-patient', [AdminController::class, 'assignPatient']);
    
    // --- DASHBOARD MEDECIN ---
    Route::get('/doctor/my-patients', [MedicalProController::class, 'myPatients']);
    Route::post('/doctor/init-suivi', [CPNController::class, 'initialiserSuivi']);
    Route::post('/doctor/save-consultation', [ConsultationController::class, 'enregistrerActe']);
    Route::post('/doctor/save-consultation', [App\Http\Controllers\Api\CPNController::class, 'enregistrerActe']);
    Route::get('/medecin/stats/cpn-par-jour', [MedicalProController::class, 'getCPNHierParJour']);
    Route::get('/medecin/stats/cpn-par-jour', [MedicalProController::class, 'getCPNTotalAujourdhui']);
    // --- CIRCUIT DES CONSULTATIONS & URGENCES ---
    // (Utilisée par ton fichier React DemandeUrgence.js)
        Route::post('/appointments/request', [ConsultationController::class, 'demanderUrgence']); 
        Route::get('/patient/mon-suivi', [CPNController::class, 'getMonSuivi']);
        Route::get('/appointments', [ConsultationController::class, 'getAppointments']);
        Route::prefix('consultations')->group(function () {
        Route::post('/demarrer', [ConsultationController::class, 'demarrerSuivi']);
        Route::post('/acte', [ConsultationController::class, 'enregistrerActe']);
        Route::get('/stats', [ConsultationController::class, 'getStats']); // Le Radar !
        Route::post('/repondre-urgence', [ConsultationController::class, 'repondreUrgence']); // Clic du docteur
    });

    // --- CIRCUIT DES NOTIFICATIONS (Création et Lecture via DB::table) ---
    Route::get('/notifications', function (Request $request) {
        $notifications = DB::table('notifications')
            ->where('notifiable_id', $request->user()->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // On décode le texte JSON pour que React (NotificationBell.js) puisse le lire
        foreach ($notifications as $notification) {
            $notification->data = json_decode($notification->data);
        }

        return $notifications;
    });

    Route::post('/notifications/read', function (Request $request) {
        // Marque toutes les notifications comme lues (La cloche passe à 0)
        DB::table('notifications')
            ->where('notifiable_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marquées comme lues']);
    });

});