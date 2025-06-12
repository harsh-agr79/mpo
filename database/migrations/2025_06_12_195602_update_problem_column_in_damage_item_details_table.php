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
        Schema::table('damage_item_details', function (Blueprint $table) {
            if (Schema::hasColumn('damage_item_details', 'problem')) {
                $table->dropColumn('problem');
            }

            // Add new foreign key column
            $table->unsignedBigInteger('problem_id')->nullable()->after('batch_id');

            $table->foreign('problem_id')
                ->references('id')
                ->on('problems')
                ->onDelete('set null'); // or 'cascade' based on your need
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damage_item_details', function (Blueprint $table) {
             $table->dropForeign(['problem_id']);
            $table->dropColumn('problem_id');

        });
    }
};
