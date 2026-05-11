<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('consultations', function (Blueprint $table) {
            // On ajoute les colonnes manquantes du PowerPoint
            if (!Schema::hasColumn('consultations', 'oedemes')) {
                $table->boolean('oedemes')->default(false)->after('poids');
                $table->text('presentation')->nullable();
                $table->text('bassin')->nullable();
                $table->text('col_position')->nullable();
                $table->text('col_consistance')->nullable();
                $table->text('col_ouverture')->nullable();
                $table->boolean('ligne_brune')->default(false);
                $table->boolean('vergetures')->default(false);
                $table->text('resultat_bandelette')->nullable();
                $table->text('test_osullivan')->nullable();
                $table->text('echographie_resume')->nullable();
                $table->text('file_url')->nullable(); // Pour le téléchargement PDF/Echo
                $table->text('lieu_accouchement_prevu')->nullable();
                $table->text('accompagnateur_nom')->nullable();
                $table->timestampTz('updated_at')->nullable();
            }
        });
    }

    public function down() {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropColumn(['oedemes', 'presentation', 'bassin', 'col_position', 'col_consistance', 'col_ouverture', 'ligne_brune', 'vergetures', 'resultat_bandelette', 'test_osullivan', 'echographie_resume', 'file_url', 'lieu_accouchement_prevu', 'accompagnateur_nom']);
        });
    }
};