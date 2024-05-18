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
        Schema::create('shipping_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('silver_rate', 5, 2);
            $table->decimal('gold_rate', 5, 2);
            $table->decimal('diamond_rate', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_locations');
    }
};
