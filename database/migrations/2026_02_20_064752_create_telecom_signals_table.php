<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('telecom_signals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id')->nullable();
            $table->uuid('receiver_id')->nullable();
            $table->text('type')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });
    }
    public function down() { Schema::dropIfExists('telecom_signals'); }
};