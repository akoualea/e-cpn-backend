<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->text('key')->primary(); // Ex: 'maintenance_mode'
            $table->boolean('value')->default(false);
            $table->text('message')->nullable(); // Message à afficher aux utilisateurs
            $table->text('estimated_end')->nullable(); // Fin estimée de maintenance
            $table->timestampTz('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};