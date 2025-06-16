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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('thirdays')->nullable();
            $table->integer('fourdays')->nullable();
            $table->integer('sixdays')->nullable();
            $table->integer('nindays')->nullable();
            $table->string('activity')->nullable();
            $table->integer('bill_count')->nullable();
            $table->string('invoice_permission')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                'thirdays',
                'fourdays',
                'sixdays',
                'nindays',
                'activity',
                'bill_count',
                'invoice_permission',
                ]);
        });
    }
};
