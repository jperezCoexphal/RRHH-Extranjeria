<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cno_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->string('description');
            $table->string('group_code', 4)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cno_codes');
    }
};
