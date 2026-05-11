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
        Schema::create('notifications', function (Blueprint $table) {
            // ID de la notification (UUID)
            $table->uuid('id')->primary();
            
            // Le type de notification (ex: App\Notifications\CPNReminder)
            $table->string('type');

            // C'EST ICI LA PARTIE CRUCIALE POUR TES UUID :
            // On définit manuellement le polymorphisme pour accepter les UUID de tes profils
            $table->string('notifiable_type');
            $table->uuid('notifiable_id'); 
            $table->index(['notifiable_type', 'notifiable_id']);
            // Les données de la notification (Titre, message, etc.) stockées en JSON
            $table->text('data');

            // Date de lecture (NULL si non lu)
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};