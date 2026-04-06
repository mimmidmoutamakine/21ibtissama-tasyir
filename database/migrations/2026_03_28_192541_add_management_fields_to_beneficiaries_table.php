<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiaries', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }

            if (! Schema::hasColumn('beneficiaries', 'can_assigned_educator_edit')) {
                $table->boolean('can_assigned_educator_edit')->default(false)->after('primary_educator_id');
            }

            if (! Schema::hasColumn('beneficiaries', 'left_at')) {
                $table->timestamp('left_at')->nullable()->after('exit_date');
            }

            if (! Schema::hasColumn('beneficiaries', 'left_reason')) {
                $table->text('left_reason')->nullable()->after('left_at');
            }

            if (! Schema::hasColumn('beneficiaries', 'left_by')) {
                $table->unsignedBigInteger('left_by')->nullable()->after('left_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            foreach (['is_active', 'can_assigned_educator_edit', 'left_at', 'left_reason', 'left_by'] as $column) {
                if (Schema::hasColumn('beneficiaries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};