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
        Schema::table('profiles', function (Blueprint $table) {
            // Ajout des colonnes pour le nom du papa et de la maman
            $table->string('papa_nom')->nullable()->after('nom');
            $table->string('maman_nom')->nullable()->after('papa_nom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Suppression des colonnes si on annule la migration
            $table->dropColumn(['papa_nom', 'maman_nom']);
        });
    }
};