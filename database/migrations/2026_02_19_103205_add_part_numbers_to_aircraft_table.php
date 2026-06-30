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
            $table->string('pn_adult')->nullable()->after('status');
            $table->string('pn_crew')->nullable()->after('pn_adult');
            $table->string('pn_infant')->nullable()->after('pn_crew');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aircraft', function (Blueprint $table) {
            $table->dropColumn(['pn_adult', 'pn_crew', 'pn_infant']);
        });
    }
};
