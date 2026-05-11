<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary(); // ID unique UUID
            $table->uuid('patient_id')->unique(); // Une patiente a un seul budget
            
            $table->decimal('somme_estimee', 12, 2)->nullable();
            $table->decimal('epargne_actuelle', 12, 2)->nullable();
            $table->text('decisionnaire')->nullable(); // Qui décide des dépenses
            
            $table->timestampTz('updated_at')->useCurrent();

            // Lien avec la table profiles
            $table->foreign('patient_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
}; 