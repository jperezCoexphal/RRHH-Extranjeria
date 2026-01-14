<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración compactada para tablas geográficas
 * Incluye: countries, provinces, municipalities
 * Debe ejecutarse ANTES de foreigners y addresses
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla: countries (países)
        /*-----------------------------------------------------------------------------------*/
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('country_name', 100)->unique('country_name_UNIQUE');
            $table->char('iso_code_2', 2)->unique('iso_code_2_UNIQUE');
        });


        // Tabla: provinces (provincias)
        /*-----------------------------------------------------------------------------------*/
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('province_name', 50)->unique('province_name_UNIQUE');
            $table->char('province_code', 2)->unique('province_code_UNIQUE');
            $table->foreignId('country_id')
                ->constrained('countries')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });


        // Tabla: municipalities (municipios)
        /*-----------------------------------------------------------------------------------*/
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->string('municipality_name', 100);
            $table->char('municipality_code', 5);
            $table->foreignId('province_id')
                ->constrained('provinces')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Índice único compuesto (código + provincia)
            $table->unique(['municipality_code', 'province_id'], 'municipality_code_province_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipalities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('countries');
    }
};
