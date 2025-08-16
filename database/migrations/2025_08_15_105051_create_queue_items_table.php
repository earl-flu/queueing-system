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
        Schema::create('queue_items', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number'); // DEN001, OB001, etc. - Not unique, resets daily per department
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('original_department_id')->constrained('departments')->onDelete('cascade'); // Original department for queue number
            $table->foreignId('current_department_id')->constrained('departments')->onDelete('cascade'); // Current department
            $table->foreignId('served_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['waiting', 'serving', 'done', 'transferred'])->default('waiting');
            $table->integer('queue_position');
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['current_department_id', 'status', 'queue_position']);
            $table->index(['original_department_id', 'created_at']);
            $table->index(['queue_number', 'original_department_id', 'created_at']); // For finding queue items by number and date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_items');
    }
};
