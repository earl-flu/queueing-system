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
        Schema::table('queue_items', function (Blueprint $table) {
            $table->timestamp('waiting_started_at')->nullable()->after('completed_at');
            $table->timestamp('serving_started_at')->nullable()->after('waiting_started_at');
            $table->integer('waiting_duration_seconds')->nullable()->after('serving_started_at');
            $table->integer('serving_duration_seconds')->nullable()->after('waiting_duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_items', function (Blueprint $table) {
            $table->dropColumn([
                'waiting_started_at',
                'serving_started_at',
                'waiting_duration_seconds',
                'serving_duration_seconds'
            ]);
        });
    }
};
