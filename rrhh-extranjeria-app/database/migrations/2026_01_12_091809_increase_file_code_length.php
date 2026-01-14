<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inmigration_files', function (Blueprint $table) {
            $table->string('file_code', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('inmigration_files', function (Blueprint $table) {
            $table->string('file_code', 12)->change();
        });
    }
};
