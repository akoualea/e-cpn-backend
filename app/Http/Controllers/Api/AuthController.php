<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail; // IMPORT INDISPENSABLE POUR L'ENVOI DE MAIL
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * CONNEXION MANUELLE AVEC DÉTECTION HYBRIDE (GOOGLE)
     */
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);
        $email = strtolower(trim($request->email));

        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(['error' => 'Identifiants incorrects.'], 401);
            }

            // Détection si le compte a été créé via Google (pas de mot de passe ou flag spécial)
            if (empty($user->password) || str_starts_with($user->password, '$2y$12$GOOGLE')) {
                return response()->json([
                    'error' => 'Ce compte est lié à Google.',
                    'suggestion' => 'Veuillez utiliser le bouton "Continuer avec Google" ou réinitialiser votre mot de passe.'
                ], 403);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Identifiants incorrects.'], 401);
            }

            if ($user->role === 'PATIENT') {
                Patient::firstOrCreate(['id' => $user->id]);
            }

            $token = Auth::guard('api')->login($user);

            return response()->json([
                'status' => 'SUCCESS',
                'token' => $token,
                'profile' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur Serveur.'], 500);
        }
    }

    /**
     * MOT DE PASSE OUBLIÉ (Partie 1 : Envoi du lien par email)
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Si ce compte existe, un email a été envoyé.']);
        }

        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $url = "http://localhost:5173/reset-password?token=".$token."&email=".$user->email;
        
        Mail::send([], [], function ($message) use ($user, $url) {
            $message->to($user->email)
                ->subject('Sécurisez votre compte e-CPN Bénin')
                ->html("
                    <div style='font-family: sans-serif; padding: 20px; color: #333;'>
                        <h1 style='color: #FF7096;'>Bonjour {$user->prenom},</h1>
                        <p>Vous avez demandé à définir ou réinitialiser votre mot de passe e-CPN.</p>
                        <p>Cliquez sur le lien ci-dessous pour sécuriser votre accès :</p>
                        <div style='margin: 30px 0;'>
                            <a href='{$url}' style='background:#FF7096; color:white; padding:15px 25px; text-decoration:none; border-radius:10px; font-weight: bold;'>DÉFINIR MON MOT DE PASSE</a>
                        </div>
                        <p>Ce lien expirera dans 60 minutes.</p>
                        <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
                    </div>
                ");
        });

        return response()->json(['message' => 'Lien de sécurisation envoyé par email.']);
    }

    /**
     * RÉINITIALISATION RÉELLE DU MOT DE PASSE (Partie 2 : Mise à jour en base)
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed', // Vérifie que password_confirmation est présent et identique
        ]);

        // 1. Vérifier si le jeton existe pour cet email
        $reset = DB::table('password_reset_tokens')
                   ->where('email', $request->email)
                   ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return response()->json(['error' => 'Ce lien est invalide ou a expiré.'], 400);
        }

        // 2. Mettre à jour la table profiles (ton modèle User pointe sur 'profiles')
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // 3. Supprimer le jeton pour qu'il ne soit plus réutilisable
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Votre mot de passe a été mis à jour avec succès.']);
    }

    /**
     * CONNEXION VIA GOOGLE
     */
    public function googleLogin(Request $request) 
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId()]);
                $token = Auth::guard('api')->login($user);
                $user->load(['patient.assignedPro.profile', 'medicalPro']); 

                return response()->json([
                    'status' => 'SUCCESS',
                    'token' => $token,
                    'profile' => $user
                ]);
            }
            
            return response()->json([
                'status' => 'NEW_USER',
                'google_data' => [
                    'id' => (string) Str::uuid(),
                    'email' => $googleUser->getEmail(),
                    'nom' => $googleUser->user['family_name'] ?? '',
                    'prenom' => $googleUser->user['given_name'] ?? '',
                    'google_id' => $googleUser->getId()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur Google : ' . $e->getMessage()], 500);
        }
    }

    /**
     * INSCRIPTION
     */
    public function register(Request $request)
    {
        $messages = [
            'email.unique' => 'Cet email est déjà utilisé par une autre maman.',
            'matricule.unique' => 'Ce matricule appartient déjà à un médecin.',
        ];

        $request->validate([
            'email'    => 'required|email|unique:profiles,email', 
            'password' => 'required|min:6',
            'nom'      => 'required',
            'prenom'   => 'required',
            'role'     => 'required|in:PATIENT,PRO'
        ], $messages);

        try {
            return DB::transaction(function () use ($request) {
                $userId = (string) Str::uuid();

                DB::table('profiles')->updateOrInsert(
                    ['email' => $request->email],
                    [
                        'id'       => $userId,
                        'nom'      => $request->nom,
                        'prenom'   => $request->prenom,
                        'role'     => $request->role,
                        'password' => Hash::make($request->password),
                        'matricule' => $request->matricule,
                        'created_at' => now(),
                    ]
                );

                if ($request->role === 'PATIENT') {
                    DB::table('patients')->updateOrInsert(['id' => $userId], ['updated_at' => now()]);
                } else {
                    DB::table('medical_pros')->updateOrInsert(
                        ['id' => $userId],
                        ['specialite' => $request->specialite, 'is_verified' => false]
                    );
                }

                $user = User::find($userId);
                $token = Auth::guard('api')->login($user);

                return response()->json(['status' => 'SUCCESS', 'token' => $token, 'profile' => $user], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la création du compte.'], 500);
        }
    }
public function me()
{
    $user = Auth::guard('api')->user();
    if (!$user) return response()->json(['error' => 'Non autorisé'], 401);

    $profile = User::find($user->id);

    if ($user->role === 'PRO') {
        $profile->load('medicalPro');
    } elseif ($user->role === 'PATIENT') {
        //  ON AJOUTE 'patient.pregnancy_infos' 
        $profile->load([
            'patient.assignedPro.profile', 
            'patient.pregnancy_infos' => function($q) {
                $q->where('is_active', true); // On ne prend que la grossesse en cours
            }
        ]);
    }

    return response()->json($profile);
}

    public function logout()
    {
        try {
            Auth::guard('api')->logout();
            return response()->json(['message' => 'Déconnecté avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur déconnexion'], 500);
        }
    }

    public function getAllPatients()
    {
        try {
            $patients = User::where('role', 'PATIENT')->get();
            return response()->json($patients);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}