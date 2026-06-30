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
        Schema::table('aircraft', function (Blueprint $table) {
            $table->foreignId('airline_id')->nullable()->after('registration')->constrained('airlines')->nullOnDelete();
        });

        // Set all existing aircraft to Garuda Indonesia (id=1)
        DB::table('aircraft')->whereNull('airline_id')->update(['airline_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            $table->dropForeign(['airline_id']);
            $table->dropColumn('airline_id');
        });
    }
};
