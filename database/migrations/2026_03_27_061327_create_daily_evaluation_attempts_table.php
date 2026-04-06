<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_evaluation_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_evaluation_entry_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('status_color')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamps();

            $table->unique(['daily_evaluation_entry_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_evaluation_attempts');
    }
};