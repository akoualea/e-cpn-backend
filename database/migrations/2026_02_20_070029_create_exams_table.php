<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id')->nullable();
            $table->uuid('pro_id')->nullable(); // Le médecin qui a prescrit/reçu
            $table->text('titre')->nullable(); // Ex: Échographie 1er trimestre
            $table->text('type_examen')->nullable(); // Biologie, Imagerie, etc.
            $table->text('fichier_url')->nullable(); // Lien vers le document (Storage)
            $table->timestampTz('created_at')->useCurrent();// Clés étrangères
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('pro_id')->references('id')->on('medical_pros')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};