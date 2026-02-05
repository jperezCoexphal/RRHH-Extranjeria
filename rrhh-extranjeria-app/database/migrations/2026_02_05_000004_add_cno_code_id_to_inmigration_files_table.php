<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inmigration_files', function (Blueprint $table) {
            $table->foreignId('cno_code_id')
                ->nullable()
                ->after('job_title')
                ->constrained('cno_codes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inmigration_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cno_code_id');
        });
    }
};
