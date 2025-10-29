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
            $table->string('image_path')->nullable()->after('image_url');
            $table->string('thumbnail_url')->nullable()->after('image_path');
            $table->string('thumbnail_path')->nullable()->after('thumbnail_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'thumbnail_url', 'thumbnail_path']);
        });
    }
};
