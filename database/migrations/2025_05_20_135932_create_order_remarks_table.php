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
        Schema::create('order_remarks', function (Blueprint $table) {
            $table->id();
            $table->string('orderid', 191);
            $table->foreign('orderid')->references('orderid')->on('orders')->onDelete('cascade');
            $table->longText('remark');
            $table->foreignId('remarks_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_remarks');
    }
};
