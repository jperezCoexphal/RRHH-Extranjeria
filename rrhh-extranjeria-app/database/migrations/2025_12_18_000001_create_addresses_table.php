<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para tabla de direcciones (polimórfica)
 * Puede pertenecer a: Employer, Foreigner, User, InmigrationFile
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();

            // Columnas polimórficas
            $table->unsignedBigInteger('addressable_id');
            $table->string('addressable_type', 50);

            // Datos de la dirección
            $table->char('postal_code', 5);
            $table->string('street_name', 150);
            $table->string('number', 10)->nullable();
            $table->string('floor_door', 20)->nullable();

            // Relaciones con tablas geográficas
            $table->foreignId('country_id')
                ->constrained('countries')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreignId('province_id')
                ->nullable()
                ->constrained('provinces')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreignId('municipality_id')
                ->nullable()
                ->constrained('municipalities')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            // Índice para búsquedas polimórficas
            $table->index(['addressable_type', 'addressable_id'], 'addresses_addressable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
