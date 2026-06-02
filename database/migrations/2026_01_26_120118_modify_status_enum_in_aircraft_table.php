<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // We use raw statement because Doctrine DBAL has issues modifying ENUMs directly in some Laravel versions
            // This forces the column to only accept 'active' and 'prolong'
            DB::statement("ALTER TABLE aircraft MODIFY COLUMN status ENUM('active', 'prolong') NOT NULL DEFAULT 'active'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            // Revert back to include preservation just in case rollback is needed
            DB::statement("ALTER TABLE aircraft MODIFY COLUMN status ENUM('active', 'prolong', 'preservation') NOT NULL DEFAULT 'active'");
        }
    }
};
