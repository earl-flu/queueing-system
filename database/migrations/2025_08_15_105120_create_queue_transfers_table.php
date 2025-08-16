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
        Schema::create('queue_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('to_department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('transferred_by')->constrained('users')->onDelete('cascade');
            $table->string('new_queue_number');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_transfers');
    }
};
