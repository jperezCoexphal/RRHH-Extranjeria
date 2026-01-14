<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inmigration_files', function (Blueprint $table) {
            $table->string('job_title', 50)->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->foreignId('editor_id')->nullable()->change();
            $table->foreignId('employer_id')->nullable()->change();
            $table->foreignId('foreigner_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inmigration_files', function (Blueprint $table) {
            $table->string('job_title', 50)->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
            $table->foreignId('editor_id')->nullable(false)->change();
            $table->foreignId('employer_id')->nullable(false)->change();
            $table->foreignId('foreigner_id')->nullable(false)->change();
        });
    }
};
