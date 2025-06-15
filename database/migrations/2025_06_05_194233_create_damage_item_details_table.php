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
        Schema::create('damage_item_details', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id', 161);
            $table->foreign('invoice_id')->references('invoice_id')->on('damages')->onDelete('cascade');
            $table->foreignId('damage_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('problem')->nullable();
            $table->string('solution')->nullable();
            $table->string('condition')->nullable();
            $table->string('warranty')->nullable();
            $table->string('warrantyproof')->nullable();
            $table->unsignedBigInteger('replaced_part')->nullable();
            $table->foreign('replaced_part')->references('id')->on('parts')->onDelete('set null');
            $table->unsignedBigInteger('replaced_product')->nullable();
            $table->foreign('replaced_product')->references('id')->on('products')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_item_details');
    }
};
