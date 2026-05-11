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
        Schema::create('journals', function (Blueprint $table) {
            // Utilisation de UUID pour l'ID car c'est ce que Supabase utilisait
            $table->uuid('id')->primary(); 
            
            // Lien avec la patiente (table profiles)
            $table->uuid('patient_id');
            
            // Contenu du journal
            $table->text('titre')->nullable();
            $table->text('note');
            $table->text('humeur')->nullable(); // Pour stocker l'émoji ou le texte de l'humeur
            
            // Date avec fuseau horaire (standard Supabase)
            $table->timestampTz('created_at')->useCurrent();

            // Clé étrangère
            $table->foreign('patient_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};