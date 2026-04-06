<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_evaluation_entries', function (Blueprint $table) {
            $table->text('note')->nullable()->after('progress_percent');
        });
    }

    public function down(): void
    {
        Schema::table('daily_evaluation_entries', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};