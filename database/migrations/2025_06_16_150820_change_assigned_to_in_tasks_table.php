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
         Schema::table('tasks', function (Blueprint $table) {
            // Drop foreign key if it exists
            $table->dropForeign(['assigned_to']);
            // Drop the existing column
            $table->dropColumn('assigned_to');
        });

        Schema::table('tasks', function (Blueprint $table) {
            // Add JSON column
            $table->json('assigned_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('tasks', function (Blueprint $table) {
            // Drop the JSON column
            $table->dropColumn('assigned_to');
        });

        Schema::table('tasks', function (Blueprint $table) {
            // Recreate foreign key column (adjust type and foreign key constraint as needed)
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }
};
