<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pdf_templates', function (Blueprint $table) {
            $table->id();

            // Información básica
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('original_filename', 255);
            $table->string('storage_path', 500);

            // Clasificación del documento
            $table->string('document_type', 20); // MODELO_EX, CONTRATO, MEMORIA
            $table->string('application_type', 10)->nullable(); // EX-03, EX-10, EX-26...

            // Estado y configuración
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);

            // Mapeo de campos PDF
            $table->json('field_mapping')->nullable(); // {"campo_pdf": {"source": "worker|employer|job", "field": "nombre", "format": null}}
            $table->json('extracted_fields')->nullable(); // Campos detectados del PDF
            $table->unsignedInteger('total_fields')->default(0);

            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->index('document_type');
            $table->index('application_type');
            $table->index(['document_type', 'application_type', 'is_default']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_templates');
    }
};
