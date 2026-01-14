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
        Schema::table('users', function (Blueprint $table) {
            $table->string('legal_name', 100)->nullable()->after('last_name');
            $table->string('dni', 9)->unique()->nullable()->after('legal_name');
            $table->string('phone_number', 30)->nullable()->after('dni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->dropColumn(['legal_name', 'dni', 'phone_number']);
        });
    }
};
