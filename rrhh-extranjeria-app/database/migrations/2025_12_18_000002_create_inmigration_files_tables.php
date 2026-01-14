<?php

use App\Enums\ApplicationType;
use App\Enums\ImmigrationFileStatus;
use App\Enums\TargetEntity;
use App\Enums\WorkingDayType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración compactada para expedientes de inmigración
 * Incluye: inmigration_files, requirement_templates, file_requirements
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla: requirement_templates (plantillas de requisitos)
        // Se crea primero porque file_requirements la referencia
        /*-----------------------------------------------------------------------------------*/
        Schema::create('requirement_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->enum('target_entity', TargetEntity::values())->nullable();
            $table->enum('application_type', ApplicationType::values())->nullable();
            $table->enum('trigger_status', ImmigrationFileStatus::values())->nullable();
            $table->integer('days_to_expire')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->softDeletes();
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index('application_type', 'req_templates_app_type_index');
            $table->index('trigger_status', 'req_templates_trigger_status_index');
        });


        // Tabla: inmigration_files (expedientes de inmigración)
        /*-----------------------------------------------------------------------------------*/
        Schema::create('inmigration_files', function (Blueprint $table) {
            $table->id();
            $table->char('campaign', 9);  // Formato: 2025-2026
            $table->string('file_code', 12)->unique('file_code_UNIQUE');
            $table->string('file_title', 165);
            $table->enum('application_type', ApplicationType::values());
            $table->enum('status', ImmigrationFileStatus::values())->default('borrador');

            // Datos laborales
            $table->string('job_title', 50);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('working_day_type', WorkingDayType::values())->nullable();
            $table->float('working_hours')->nullable();
            $table->integer('probation_period')->nullable();

            // Relaciones
            $table->foreignId('editor_id')
                ->constrained('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreignId('employer_id')
                ->constrained('employers')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreignId('foreigner_id')
                ->constrained('foreigners')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->softDeletes();
            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index('campaign', 'inmig_files_campaign_index');
            $table->index('status', 'inmig_files_status_index');
            $table->index('application_type', 'inmig_files_app_type_index');
        });


        // Tabla: file_requirements (requisitos del expediente)
        /*-----------------------------------------------------------------------------------*/
        Schema::create('file_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->text('description')->nullable();
            $table->enum('target_entity', TargetEntity::values())->nullable();
            $table->text('observation')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_mandatory')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('notified_at')->nullable();

            // Relaciones
            $table->foreignId('inmigration_file_id')
                ->constrained('inmigration_files')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreignId('requirement_template_id')
                ->nullable()
                ->constrained('requirement_templates')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->timestamps();

            // Índices para búsquedas frecuentes
            $table->index('is_completed', 'file_reqs_completed_index');
            $table->index('is_mandatory', 'file_reqs_mandatory_index');
            $table->index('due_date', 'file_reqs_due_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_requirements');
        Schema::dropIfExists('inmigration_files');
        Schema::dropIfExists('requirement_templates');
    }
};
