<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpn_protocol', function (Blueprint $table) {
            $table->id();
            $table->uuid('patient_id');
            $table->integer('cpn_number'); // 1 à 8
            $table->date('date_theorique'); // Calculée par Laravel (DDR + X semaines)
            $table->string('status')->default('scheduled'); // scheduled, completed
            $table->timestamps();
            // Lien avec la table profiles
            $table->foreign('patient_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpn_protocol');
    }
};