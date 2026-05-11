<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('profiles', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->text('nom')->nullable();
        $table->text('prenom')->nullable();
        $table->text('email')->unique();
        $table->text('password'); // <--- CETTE LIGNE DOIT ÊTRE LÀ
        $table->text('role')->nullable(); 
        $table->text('matricule')->nullable();
        $table->text('google_id')->nullable();
        $table->timestampTz('created_at')->useCurrent();
    });
}

    public function down()
    {
        Schema::dropIfExists('profiles');
    }
    
};