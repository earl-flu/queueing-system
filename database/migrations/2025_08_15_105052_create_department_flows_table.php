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
        Schema::create('department_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('final_department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('step_department_id')->constrained('departments')->onDelete('cascade');
            $table->integer('step_order'); // 1, 2, 3, etc.
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->unique(['final_department_id', 'step_department_id']);
            $table->index(['final_department_id', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_flows');
    }
};
