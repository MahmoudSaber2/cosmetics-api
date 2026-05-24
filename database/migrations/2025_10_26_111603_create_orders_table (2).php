<?php

use App\Enums\DiscountTypeEnum;
use App\Enums\OrderPaidEnum;
use App\Enums\OrderStatusEnum;
use App\Traits\CreatedUpdatedByMigration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatedUpdatedByMigration;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('client_id')->constrained()->onDelete('restrict');
            $table->decimal('total_amount', 10, 3)->default(0);
            $table->decimal('total_cost', 10, 3)->default(0);
            $table->decimal('discount', 10, 3)->default(0);
            $table->decimal('total_after_discount', 10, 3)->default(0);
            $table->tinyInteger('discount_type')->default(DiscountTypeEnum::NO_DISCOUNT->value);
            $table->tinyInteger('status')->default(OrderStatusEnum::PENDING->value);
            $table->tinyInteger('payment_status')->default(OrderPaidEnum::UNPAID->value);
            $table->text('note')->nullable();
            $table->text('rejection_reason')->nullable();
            $this->createdUpdatedByRelationship($table);
            $table->timestamps();
            $table->softDeletes();
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
