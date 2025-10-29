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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand');
            $table->string('type'); // foundation, lipstick, perfume, etc.
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->enum('gender', ['men', 'women', 'unisex'])->default('unisex');
            $table->decimal('price', 10, 2);
            $table->string('image_url')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
