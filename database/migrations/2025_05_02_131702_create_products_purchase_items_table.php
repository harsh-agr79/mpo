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
        Schema::create('products_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_id');
            $table->foreign('purchase_id')->references('purchase_id')->on('products_purchases')->onDelete('cascade');
            $table->string('prod_unique_id');
            $table->foreign('prod_unique_id')->references('prod_unique_id')->on('products');
            $table->integer('quantity');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_purchase_items');
    }
};
