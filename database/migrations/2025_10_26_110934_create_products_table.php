<?php

use App\Enums\ProductStatusEnum;
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
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->decimal('cost', 10, 3)->nullable();
            $table->decimal('price', 10, 3);
            $table->tinyInteger('status')->default(ProductStatusEnum::ACTIVE->value);
            $table->smallInteger('min_stock')->default(0);
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
