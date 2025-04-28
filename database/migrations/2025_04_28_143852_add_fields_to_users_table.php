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
            $table->string('contact')->unique()->after('email');
            $table->foreignId('role_id')->constrained()->after('contact');
            $table->enum('type', ['Dealer', 'Wholesaler', 'Retailer', 'Ecommerce'])->after('role_id');
            $table->boolean('disabled')->default(false)->after('type');
            $table->string('shop_name')->nullable();
            $table->string('address')->nullable();
            $table->string('area')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->integer('marketer_id')->nullable();
            $table->float('open_balance')->default(0);
            $table->float('balance')->default(0);
            $table->string('profile_image')->nullable();
            $table->string('secondary_contact')->unique()->nullable();
            $table->date('dob')->nullable();
            $table->enum('tax_type', ['VAT', 'PAN'])->nullable();
            $table->string('tax_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
