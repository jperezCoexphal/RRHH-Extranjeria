<?php

use App\Enums\LegalForm;
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
        
        // Father table: ``employers``
        /*-----------------------------------------------------------------------------------*/
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->enum('legal_form', LegalForm::values());
            $table->string('comercial_name', 100)->nullable();
            $table->string('fiscal_name', 100)->unique('fiscal_name_UNIQUE');
            $table->string('nif', 9)->unique('nif_UNIQUE');
            $table->string('ccc', 11);
            $table->string('cnae', 5);
            $table->string('email', 255)->unique('email_UNIQUE')->nullable();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_associated');
            $table->softDeletes();
            $table->timestamps();
        });


        // Son table: ``freelancers``
        /*-----------------------------------------------------------------------------------*/
        Schema::create('freelancers', function (Blueprint $table) {
            $table->foreignId('employer_id')
                ->primary()
                ->constrained('employers')
                ->onDelete('cascade');
            $table->string('first_name', 50);
            $table->string('last_name', 100);
            $table->string('niss', 12)->unique('niss_UNIQUE');
            $table->date('birthdate');
        });


        // Son table: ``companies``
        /*-----------------------------------------------------------------------------------*/
        Schema::create('companies', function (Blueprint $table) {
            $table->foreignId('employer_id')
                ->primary()
                ->constrained('employers')
                ->onDelete('cascade');
            $table->string('representative_name', 150);
            $table->string('representative_title', 100);
            $table->string('representantive_identity_number', 9)->unique('representative_dni_UNIQUE');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
        Schema::dropIfExists('freelancers');
        Schema::dropIfExists('employers');
    }
};
