<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pregnancy_infos', function (Blueprint $table) {
            $table->id(); 
            $table->uuid('patient_id');
            $table->uuid('doctor_id');
            $table->date('ddr_date'); 
            $table->date('date_accouchement_prevue');
            $table->integer('current_cpn')->default(0);
            
            // Pour savoir quelle grossesse afficher sur le dashboard (cas de grossesses multiples)
            $table->boolean('is_active')->default(true); 
            
            $table->timestamps();

            // Clés étrangères vers la table profiles
            $table->foreign('patient_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pregnancy_infos');
    }
};