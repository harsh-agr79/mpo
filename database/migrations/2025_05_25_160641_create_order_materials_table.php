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
        Schema::create('order_materials', function (Blueprint $table) {
            $table->id();
            $table->string('orderid', 191);
            $table->foreign('orderid')->references('orderid')->on('orders')->onDelete('cascade');

            // Foreign key to materials table
            
           $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('quantity');
            $table->string('status'); // could be 'pending', 'shipped', etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_materials');
    }
};
