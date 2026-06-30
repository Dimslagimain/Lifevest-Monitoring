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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->string('registration', 10); // PK-GFD, PK-GIA, etc.
            $table->string('seat_id', 10); // 6A, 21C, captain, etc.
            $table->integer('row')->nullable(); // Row number (null for cockpit)
            $table->string('col', 5)->nullable(); // Column letter
            $table->string('class_type', 20)->default('economy'); // cockpit, first, business, economy
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            // Composite unique index
            $table->unique(['registration', 'seat_id']);
            $table->index('registration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
