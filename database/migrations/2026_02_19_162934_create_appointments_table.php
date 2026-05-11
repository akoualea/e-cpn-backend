<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Création de la table des rendez-vous.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // IDs de type UUID pour être compatible avec Supabase Auth
            $table->uuid('patient_id')->index(); 
            $table->uuid('doctor_id')->index();
            
            $table->dateTime('scheduled_at')->index();
            $table->string('type'); // Exemple: CPN, Échographie, Urgence
            
            // Numéro de la consultation (1 à 8 selon l'OMS)
            $table->integer('cpn_number')->nullable(); 
            
            // Cycle de vie du RDV
            $table->enum('status', [
                'scheduled',    // Planifié par le doc
                'pending',      // En attente (demande femme enceinte)
                'checked_in',   // Patiente arrivée (salle d'attente)
                'in_progress',  // En cours de visio
                'completed',    // Terminé
                'cancelled'     // Annulé
            ])->default('scheduled');
            
            $table->boolean('is_online')->default(true);
            $table->text('reason')->nullable(); 
            
            // Système de rappel
            $table->boolean('is_notified')->default(false); 
            
            $table->timestamps(); // created_at et updated_at
        });
    }

    /**
     * Suppression de la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};