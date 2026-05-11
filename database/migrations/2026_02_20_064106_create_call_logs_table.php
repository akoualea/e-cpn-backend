<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('call_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('caller_id')->nullable();
            $table->uuid('receiver_id')->nullable();
            $table->text('status')->nullable();
            $table->timestampTz('started_at')->useCurrent();
            $table->timestampTz('ended_at')->nullable();
            
            $table->foreign('caller_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('profiles')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('call_logs'); }
};