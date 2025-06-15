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
        Schema::create('damages', function (Blueprint $table) {
            $table->id();
            $table->datetime('date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_id', 161)->unique();
            $table->string('cusuni_id')->nullable();
            $table->datetime('sendbycus')->nullable();
            $table->datetime('recbycomp')->nullable();
            $table->datetime('sendbackbycomp')->nullable();
            $table->datetime('recbycus')->nullable();
            $table->integer('mainstatus');
            $table->longtext('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damages');
    }
};
