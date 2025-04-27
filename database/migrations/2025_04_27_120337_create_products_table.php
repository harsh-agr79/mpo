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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('subcategory_id')->references('id')->on('sub_categories')->onDelete('cascade');
            $table->decimal('price');
            $table->integer('stock');
            $table->string('prod_unique_id')->unique();
            $table->string('offer')->nullable();
            $table->string('image')->nullable();
            $table->string('image_2')->nullable();
            $table->string('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
