<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('medical_pros', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('matricule')->unique()->nullable();
            $table->text('specialite')->nullable();
            
            $table->boolean('is_verified')->default(false);
            $table->text('rejection_reason')->nullable();
            $table->foreign('id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('medical_pros'); }
};