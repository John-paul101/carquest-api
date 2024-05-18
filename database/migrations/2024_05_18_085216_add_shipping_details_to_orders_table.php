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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_location_id');
            $table->unsignedBigInteger('shipping_method_id');

            $table->foreign('shipping_location_id')->references('id')->on('shipping_locations');
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropForeign(['shipping_location_id']);
        $table->dropForeign(['shipping_method_id']);

        $table->dropColumn('shipping_location_id');
        $table->dropColumn('shipping_method_id');
    });
}
};
