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
        Schema::create('medicine_stocks', function (Blueprint $table) {
           $table->id('stock_id');
        $table->unsignedBigInteger('inventory_id');
        $table->unsignedBigInteger('medicine_category_id');
        $table->integer('quantity_in_stock');
        $table->date('last_restocked')->nullable();
        $table->date('expiration_date')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_stocks');
    }
};
