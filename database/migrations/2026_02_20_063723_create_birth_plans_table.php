<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birth_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id')->unique(); // Un seul plan par patiente
            
            $table->text('lieu_prevu')->nullable(); // Hôpital choisi
            $table->text('moyen_transport')->nullable(); // Taxi, voiture, ambulance
            $table->text('accompagnant_nom')->nullable(); // Qui l'accompagne
            $table->text('donneur_sang')->nullable(); // Donneur potentiel identifié
            
            $table->timestampTz('updated_at')->useCurrent();

            // Lien avec la table profiles
            $table->foreign('patient_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birth_plans');
    }
};