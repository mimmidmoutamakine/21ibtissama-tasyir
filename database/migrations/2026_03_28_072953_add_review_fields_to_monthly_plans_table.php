<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_plans', 'status')) {
                $table->string('status', 30)->default('draft');
            }

            if (!Schema::hasColumn('monthly_plans', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable();
            }

            if (!Schema::hasColumn('monthly_plans', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }

            if (!Schema::hasColumn('monthly_plans', 'revision_summary')) {
                $table->text('revision_summary')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_plans', function (Blueprint $table) {
            foreach (['status', 'submitted_at', 'approved_at', 'revision_summary'] as $column) {
                if (Schema::hasColumn('monthly_plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};