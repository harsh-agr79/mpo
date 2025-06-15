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
           if (Schema::hasColumn('damage_item_details', 'replaced_part')) {
                // You may need to manually name the foreign key if it differs
                $table->dropForeign(['replaced_part']);
            }

            // Change replaced_part to longText (if column already exists)
            if (Schema::hasColumn('damage_item_details', 'replaced_part')) {
                $table->longText('replaced_part')->change();
            }

            // Add remarks field
            if (!Schema::hasColumn('damage_item_details', 'remarks')) {
                $table->text('remarks')->nullable()->after('replaced_part');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damage_item_details', function (Blueprint $table) {
           $table->unsignedBigInteger('replaced_part')->change();

            // Optionally re-add foreign key (update 'parts' if that's the original table)
            $table->foreign('replaced_part')->references('id')->on('parts')->onDelete('set null');

            // Drop remarks field
            $table->dropColumn('remarks');
        });
    }
};
