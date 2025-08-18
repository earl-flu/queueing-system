<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_window', function (Blueprint $table) {
            $table->id();
            $table->foreignId('window_id')->constrained('windows')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['window_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_window');
    }
};
