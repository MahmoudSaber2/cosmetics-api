<?php

use App\Enums\DiscountTypeEnum;
use App\Enums\OrderStatusEnum;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 10, 3)->default(0);
            $table->decimal('total_cost', 10, 3)->default(0);
            $table->decimal('discount', 10, 3)->default(0);
            $table->decimal('total_after_discount', 10, 3)->default(0);
            $table->tinyInteger('discount_type')->default(DiscountTypeEnum::NO_DISCOUNT->value);
            $table->tinyInteger('status')->default(OrderStatusEnum::PENDING->value);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
