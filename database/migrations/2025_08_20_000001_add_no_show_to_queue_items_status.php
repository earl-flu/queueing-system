<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update enum to include no_show (MySQL-compatible). If using SQLite, this will be a no-op.
        try {
            DB::statement("ALTER TABLE queue_items MODIFY status ENUM('waiting','serving','done','transferred','skipped','no_show') DEFAULT 'waiting'");
        } catch (\Throwable $e) {
            // Ignore if the database does not support ALTERing enum (e.g., SQLite in tests)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE queue_items MODIFY status ENUM('waiting','serving','done','transferred') DEFAULT 'waiting'");
        } catch (\Throwable $e) {
            // Ignore if not supported
        }
    }
};
