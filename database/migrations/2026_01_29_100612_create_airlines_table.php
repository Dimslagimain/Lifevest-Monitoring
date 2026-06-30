<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('airlines', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Garuda Indonesia, Citilink
            $table->string('code', 10)->nullable(); // GA, QG
            $table->string('icon')->default('🏢');
            $table->timestamps();
        });

        // Insert default airlines
        DB::table('airlines')->insert([
            ['name' => 'Garuda Indonesia', 'code' => 'GA', 'icon' => '🦅', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airlines');
    }
};
