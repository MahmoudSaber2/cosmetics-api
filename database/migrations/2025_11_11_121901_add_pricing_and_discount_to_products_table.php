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
        Schema::table('products', function (Blueprint $table) {
            // Rename price to selling_price
            $table->renameColumn('price', 'selling_price');
        });

        Schema::table('products', function (Blueprint $table) {
            // Add purchase price
            $table->decimal('purchase_price', 10, 2)->after('selling_price')->nullable();

            // Add discount fields
            $table->enum('discount_type', ['percentage', 'fixed'])->after('purchase_price')->nullable();
            $table->decimal('discount_value', 10, 2)->after('discount_type')->nullable();
            $table->timestamp('discount_start_date')->after('discount_value')->nullable();
            $table->timestamp('discount_end_date')->after('discount_start_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'purchase_price',
                'discount_type',
                'discount_value',
                'discount_start_date',
                'discount_end_date'
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('selling_price', 'price');
        });
    }
};
