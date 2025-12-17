<?php

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use App\Enums\RelationType;
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
        Schema::create('foreigners', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 100);
            $table->string('passport', 44)->unique('passport_UNIQUE');
            $table->string('nie', 9)->unique('nie_UNIQUE');
            $table->string('niss', 12)->unique('niss_UNIQUE')->nullable();
            $table->enum('gender', Gender::values());
            $table->date('birthdate');
            $table->string('nationality', 50);
            $table->enum('marital_status', MaritalStatus::values());
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('foreigners_extra_data', function (Blueprint $table) {
            $table->foreignId('foreigner_id')
                ->primary()
                ->constrained('foreigners')
                ->onDelete('cascade');
            $table->string('father_name', 150)->nullable();
            $table->string('mother_name', 150)->nullable();
            $table->string('legal_guardian_name', 150)->nullable();
            $table->string('legal_guardian_identity_number', 44)->nullable();
            $table->string('guardianship_title', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();
        });

        Schema::create('foreigner_relationships', function (Blueprint $table) {
            $table->id();
            $table->enum('relation_type', RelationType::values());
            $table->foreignId('foreigner_id')
            ->constrained('foreigners')
            ->onDelete('cascade');
            $table->foreignId('related_foreigner_id')
            ->constrained('foreigners')
            ->onDelete('cascade');
            $table->unique(['foreigner_id', 'related_foreigner_id'], 'relationship_UNIQUE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foreigner_relationships');
        Schema::dropIfExists('foreigners_extra_data');
        Schema::dropIfExists('foreigners');
    }
};
