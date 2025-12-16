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
        Schema::table('product_media', function (Blueprint $table) {
            $table->boolean('is_main')->default(false)->after('media_type');
            $table->index(['product_id', 'is_main']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_media', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'is_main']);
            $table->dropColumn('is_main');
        });
    }
};
