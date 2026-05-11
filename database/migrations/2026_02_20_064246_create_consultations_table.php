<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
     Schema::create('consultations', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('patient_id')->nullable();
    $table->uuid('doctor_id')->nullable(); // Changé pro_id -> doctor_id
    $table->integer('hauteur_uterine')->nullable(); // Changé hu_cm -> hauteur_uterine
    $table->text('bcf')->nullable(); // Changé bdc_foetal -> bcf
    $table->boolean('maf_present')->nullable();
    $table->text('tension_arterielle')->nullable(); // Changé tension -> tension_arterielle
    $table->decimal('poids', 8, 2)->nullable();
    $table->integer('cpn_number')->nullable(); // Changé cpn_numero -> cpn_number
    $table->text('pronostic')->nullable();
    $table->text('observations')->nullable();
    $table->timestampTz('created_at')->useCurrent();
    
    $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
    $table->foreign('doctor_id')->references('id')->on('medical_pros')->onDelete('cascade');
});
    }
    public function down() { Schema::dropIfExists('consultations'); }
};