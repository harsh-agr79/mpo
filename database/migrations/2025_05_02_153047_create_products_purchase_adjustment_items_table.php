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
        Schema::create('products_purchase_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_adj_id');
            $table->foreign('purchase_adj_id')->references('purchase_adj_id')->on('products_purchase_adjustments')->onDelete('cascade');
            $table->string('prod_unique_id');
            $table->foreign('prod_unique_id')->references('prod_unique_id')->on('products');
            $table->integer('quantity');
            $table->enum('type', ['increase', 'decrease']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products_purchase_adjustment_items');
    }
};
