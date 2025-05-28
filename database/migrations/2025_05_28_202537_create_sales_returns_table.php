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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->datetime('date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('return_id', 171)->unique();
            $table->string('cusuni_id')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('total')->nullable();
            $table->integer('net_total')->nullable();
            $table->longtext('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
