<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id')->nullable();
            $table->uuid('receiver_id')->nullable();
            $table->text('contenu')->nullable();
            $table->boolean('is_ia')->default(false);
            $table->text('audio_url')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->foreign('sender_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('messages'); }
};