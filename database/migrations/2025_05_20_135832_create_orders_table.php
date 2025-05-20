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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->datetime('date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('orderid', 191)->unique();
            $table->string('cusuni_id')->nullable();
            $table->longText('mainstatus')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('total')->nullable();
            $table->integer('net_total')->nullable();
            $table->boolean('save');
            $table->string('clnstatus')->nullable();
            $table->unsignedBigInteger('clntime')->nullable();
            $table->foreignId('seenby')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('delivered_at')->nullable();
            $table->string('recieved_at')->nullable();
            $table->string('nepmonth')->nullable();
            $table->string('nepyear')->nullable();
            $table->string('othersname')->nullable();
            $table->string('cartoons')->nullable();
            $table->longText('transport')->nullable();
            $table->longText('user_remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
