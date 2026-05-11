<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('id_unique_benin')->unique()->nullable();
            $table->date('ddr')->nullable();
            $table->date('dpa')->nullable();
            $table->text('gs_rh')->nullable();
            $table->text('electrophorese_hb')->nullable();
            $table->integer('gestite_g')->nullable();
            $table->integer('parite_p')->nullable();
            $table->integer('vivants_v')->nullable();
            $table->uuid('assigned_pro_id')->nullable();
            $table->text('lieu_prevu')->nullable();
            $table->text('donneur_sang')->nullable();
            $table->decimal('somme_estimee', 12, 2)->nullable();
            $table->decimal('epargne_actuelle', 12, 2)->nullable();
            $table->text('decisionnaire')->nullable();
            $table->text('transport_type')->nullable();
            $table->text('accompagnateur_nom')->nullable();
            $table->text('accompagnateur_contact')->nullable();
            $table->text('donneur_nom')->nullable();
            $table->text('donneur_contact')->nullable();
            $table->integer('prix_trousseau_mere')->default(15000);
            $table->integer('prix_trousseau_bebe')->default(25000);
            $table->integer('prix_examens')->default(30000);
            $table->integer('prix_imprevus')->default(10000);
            $table->integer('prix_cesarienne')->default(50000);
            $table->integer('epargne_trousseau_mere')->default(0);
            $table->integer('epargne_trousseau_bebe')->default(0);
            $table->integer('epargne_examens')->default(0);
            $table->integer('epargne_imprevus')->default(0);
            $table->integer('epargne_cesarienne')->default(0);
            $table->text('accompagnateur_adresse')->nullable();
            $table->text('donneur_adresse')->nullable();
            $table->timestampTz('updated_at')->useCurrent();
            
            $table->foreign('id')->references('id')->on('profiles')->onDelete('cascade');
            $table->foreign('assigned_pro_id')->references('id')->on('medical_pros');
        });
    }
    public function down() { Schema::dropIfExists('patients'); }
};