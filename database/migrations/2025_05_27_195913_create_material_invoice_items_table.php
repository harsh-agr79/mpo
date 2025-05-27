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
        Schema::create('material_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id', 181);
            $table->foreign('invoice_id')->references('invoice_id')->on('material_invoices')->onDelete('cascade');

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
        Schema::dropIfExists('material_invoice_items');
    }
};
