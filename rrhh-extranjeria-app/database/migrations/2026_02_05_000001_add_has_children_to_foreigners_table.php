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
        Schema::table('foreigners', function (Blueprint $table) {
            $table->boolean('has_children')->default(false)->after('marital_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foreigners', function (Blueprint $table) {
            $table->dropColumn('has_children');
        });
    }
};
