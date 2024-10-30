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
        Schema::create('medicine_inventories', function (Blueprint $table) {
            $table->id('inventory_id');
        $table->string('inventory_number')->unique();
        $table->unsignedBigInteger('medicine_category_id');
        $table->integer('total_medicine');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_inventories');
    }
};
