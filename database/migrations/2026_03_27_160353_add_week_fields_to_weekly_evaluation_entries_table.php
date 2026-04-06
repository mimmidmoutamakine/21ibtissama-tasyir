<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_evaluation_entries', function (Blueprint $table) {
            $table->string('week_1_status_color')->nullable()->after('monthly_plan_item_id');
            $table->unsignedTinyInteger('week_1_progress_percent')->default(0)->after('week_1_status_color');

            $table->string('week_2_status_color')->nullable()->after('week_1_progress_percent');
            $table->unsignedTinyInteger('week_2_progress_percent')->default(0)->after('week_2_status_color');

            $table->string('week_3_status_color')->nullable()->after('week_2_progress_percent');
            $table->unsignedTinyInteger('week_3_progress_percent')->default(0)->after('week_3_status_color');

            $table->string('week_4_status_color')->nullable()->after('week_3_progress_percent');
            $table->unsignedTinyInteger('week_4_progress_percent')->default(0)->after('week_4_status_color');

            $table->text('note')->nullable()->after('week_4_progress_percent');
            $table->boolean('is_manually_edited')->default(false)->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_evaluation_entries', function (Blueprint $table) {
            $table->dropColumn([
                'week_1_status_color',
                'week_1_progress_percent',
                'week_2_status_color',
                'week_2_progress_percent',
                'week_3_status_color',
                'week_3_progress_percent',
                'week_4_status_color',
                'week_4_progress_percent',
                'note',
                'is_manually_edited',
            ]);
        });
    }
};