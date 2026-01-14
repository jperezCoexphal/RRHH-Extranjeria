<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Foreigners table - make import-related fields nullable
        Schema::table('foreigners', function (Blueprint $table) {
            $table->string('passport', 44)->nullable()->change();
            $table->string('nie', 9)->nullable()->change();
            $table->string('gender')->nullable()->change();
            $table->date('birthdate')->nullable()->change();
            $table->string('marital_status')->nullable()->change();
            $table->foreignId('nationality_id')->nullable()->change();
            $table->foreignId('birth_country_id')->nullable()->change();
            $table->string('birthplace_name', 255)->nullable()->change();
        });

        // Employers table - make ccc nullable
        Schema::table('employers', function (Blueprint $table) {
            $table->string('ccc', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Revert foreigners
        Schema::table('foreigners', function (Blueprint $table) {
            $table->string('passport', 44)->nullable(false)->change();
            $table->string('nie', 9)->nullable(false)->change();
            $table->string('gender')->nullable(false)->change();
            $table->date('birthdate')->nullable(false)->change();
            $table->string('marital_status')->nullable(false)->change();
            $table->foreignId('nationality_id')->nullable(false)->change();
            $table->foreignId('birth_country_id')->nullable(false)->change();
            $table->string('birthplace_name', 255)->nullable(false)->change();
        });

        // Revert employers
        Schema::table('employers', function (Blueprint $table) {
            $table->string('ccc', 20)->nullable(false)->change();
        });
    }
};
