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
        Schema::create('parts_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->foreign('invoice_id')->references('invoice_id')->on('parts_purchases')->onDelete('cascade');
            $table->foreignId('part_id')->constrained();
            $table->string('voucher');
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
        Schema::dropIfExists('parts_purchase_items');
    }
};
