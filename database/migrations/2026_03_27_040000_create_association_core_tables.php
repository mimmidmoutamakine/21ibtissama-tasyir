<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name_ar');
            $table->string('last_name_ar');
            $table->string('first_name_fr')->nullable();
            $table->string('last_name_fr')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->string('father_full_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('father_cin')->nullable();
            $table->string('mother_full_name')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('mother_cin')->nullable();
            $table->string('guardian_cin')->nullable();
            $table->string('birth_certificate_reference')->nullable();
            $table->string('family_status')->nullable();
            $table->string('disability_type')->nullable();
            $table->string('disability_degree')->nullable();
            $table->string('disability_classification')->nullable();
            $table->string('school_level')->nullable();
            $table->string('school_institution')->nullable();
            $table->date('registration_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('benefit_type')->nullable();
            $table->string('program_service_domain')->nullable();
            $table->json('document_checklist')->nullable();
            $table->json('uploaded_documents')->nullable();
            $table->string('status')->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('full_name');
            $table->string('role_label');
            $table->string('phone')->nullable();
            $table->string('cin')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('contract_type')->nullable();
            $table->string('qualification')->nullable();
            $table->string('status')->default('active');
            $table->json('document_requirements')->nullable();
            $table->json('organization_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('internal_code')->unique();
            $table->string('first_name_ar');
            $table->string('last_name_ar');
            $table->string('first_name_fr')->nullable();
            $table->string('last_name_fr')->nullable();
            $table->string('photo_path')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->json('guardian_details')->nullable();
            $table->json('phones')->nullable();
            $table->json('disability_details')->nullable();
            $table->json('schooling_details')->nullable();
            $table->date('admission_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->string('benefit_type')->nullable();
            $table->foreignId('primary_educator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('program_name')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('beneficiary_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->string('section');
            $table->string('category');
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('needs_update')->default(false);
            $table->date('uploaded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('beneficiary_service_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('frequency')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('annual_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->string('year_label');
            $table->string('schooling_space_type')->nullable();
            $table->text('initial_situation_summary')->nullable();
            $table->json('team_participants')->nullable();
            $table->text('family_participation')->nullable();
            $table->string('approval_status')->default('approved');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('annual_project_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annual_project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('annual_project_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('annual_project_domain_id')->constrained()->cascadeOnDelete();
            $table->text('objective_text');
            $table->string('baseline_level')->nullable();
            $table->string('target_level')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('monthly_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('educator_id')->constrained('users')->cascadeOnDelete();
            $table->date('month_date');
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('revision_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('monthly_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('annual_project_objective_id')->nullable()->constrained()->nullOnDelete();
            $table->string('domain_axis');
            $table->text('monthly_objective');
            $table->text('activity');
            $table->text('resources')->nullable();
            $table->text('success_criteria')->nullable();
            $table->timestamps();
        });

        Schema::create('monthly_plan_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision');
            $table->text('remarks')->nullable();
            $table->text('changes_summary')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monthly_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('educator_id')->constrained('users')->cascadeOnDelete();
            $table->date('evaluation_date');
            $table->text('general_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_evaluation_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monthly_plan_item_id')->constrained()->cascadeOnDelete();
            $table->string('status_color')->nullable();
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('weekly_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monthly_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('educator_id')->constrained('users')->cascadeOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->timestamps();
        });

        Schema::create('weekly_evaluation_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monthly_plan_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('calculated_progress_percent', 5, 2)->default(0);
            $table->decimal('educator_progress_percent', 5, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('monthly_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monthly_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('educator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('specialist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('month_date');
            $table->text('general_remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('monthly_evaluation_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('monthly_plan_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('calculated_progress_percent', 5, 2)->default(0);
            $table->decimal('educator_progress_percent', 5, 2)->nullable();
            $table->text('educator_remarks')->nullable();
            $table->string('specialist_final_decision')->nullable();
            $table->text('specialist_remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('periodic_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->date('meeting_month');
            $table->text('general_notes')->nullable();
            $table->text('difficulties')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
        });

        Schema::create('beneficiary_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('educator_id')->constrained('users')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->boolean('is_required')->default(false);
            $table->string('file_path')->nullable();
            $table->boolean('needs_update')->default(false);
            $table->timestamps();
        });

        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('association_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('status')->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('alert_threshold')->default(0);
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->constrained()->cascadeOnDelete();
            $table->string('movement_type');
            $table->integer('quantity');
            $table->date('movement_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('archive_files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('archiveable_type');
            $table->unsignedBigInteger('archiveable_id');
            $table->string('category');
            $table->string('file_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['archiveable_type', 'archiveable_id']);
        });

        Schema::create('specialist_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiary_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialist_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->date('report_date');
            $table->string('specialty');
            $table->json('structured_fields')->nullable();
            $table->text('summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialist_reports');
        Schema::dropIfExists('archive_files');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_items');
        Schema::dropIfExists('association_projects');
        Schema::dropIfExists('employee_attendances');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('beneficiary_attendances');
        Schema::dropIfExists('periodic_meetings');
        Schema::dropIfExists('monthly_evaluation_entries');
        Schema::dropIfExists('monthly_evaluations');
        Schema::dropIfExists('weekly_evaluation_entries');
        Schema::dropIfExists('weekly_evaluations');
        Schema::dropIfExists('daily_evaluation_entries');
        Schema::dropIfExists('daily_evaluations');
        Schema::dropIfExists('monthly_plan_reviews');
        Schema::dropIfExists('monthly_plan_items');
        Schema::dropIfExists('monthly_plans');
        Schema::dropIfExists('annual_project_objectives');
        Schema::dropIfExists('annual_project_domains');
        Schema::dropIfExists('annual_projects');
        Schema::dropIfExists('beneficiary_service_assignments');
        Schema::dropIfExists('services');
        Schema::dropIfExists('beneficiary_documents');
        Schema::dropIfExists('beneficiaries');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('leads');
    }
};
