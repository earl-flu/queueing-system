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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('suffix', ['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'])->nullable();
            $table->string('phone')->nullable();
            $table->string('age')->nullable();
            $table->boolean('is_priority')->default(0);
            $table->enum('priority_category', ['PWD', 'Senior Citizen', 'Pregnant Women', 'Fever', 'Possible TB'])->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
