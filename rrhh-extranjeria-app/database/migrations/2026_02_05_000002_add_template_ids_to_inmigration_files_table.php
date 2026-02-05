<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inmigration_files', function (Blueprint $table) {
            $table->foreignId('modelo_ex_template_id')
                ->nullable()
                ->after('foreigner_id')
                ->constrained('pdf_templates')
                ->nullOnDelete();

            $table->foreignId('contrato_template_id')
                ->nullable()
                ->after('modelo_ex_template_id')
                ->constrained('pdf_templates')
                ->nullOnDelete();

            $table->foreignId('memoria_template_id')
                ->nullable()
                ->after('contrato_template_id')
                ->constrained('pdf_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inmigration_files', function (Blueprint $table) {
            $table->dropForeign(['modelo_ex_template_id']);
            $table->dropForeign(['contrato_template_id']);
            $table->dropForeign(['memoria_template_id']);
            $table->dropColumn(['modelo_ex_template_id', 'contrato_template_id', 'memoria_template_id']);
        });
    }
};
