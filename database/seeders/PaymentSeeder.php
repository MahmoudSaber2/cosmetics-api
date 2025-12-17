<?php

namespace Database\Seeders;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds for payment system.
     */
    public function run(): void
    {
        $this->command->info('Creating sample payment records...');

        // Get some orders to attach payments to
        $orders = Order::take(10)->get();

        if ($orders->isEmpty()) {
            $this->command->warn('No orders found. Please run OrderSeeder first.');
            return;
        }

        $paymentStatuses = [
            PaymentStatusEnum::SUCCEEDED,
            PaymentStatusEnum::PENDING,
            PaymentStatusEnum::FAILED,
            PaymentStatusEnum::PROCESSING,
        ];

        foreach ($orders as $index => $order) {
            $status = $paymentStatuses[$index % count($paymentStatuses)];

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => PaymentMethodEnum::STRIPE,
                'status' => $status,
                'amount' => $order->total_amount,
                'currency' => 'usd',
                'transaction_id' => $status === PaymentStatusEnum::SUCCEEDED ? 'txn_' . fake()->uuid() : null,
                'stripe_payment_intent_id' => 'pi_' . fake()->regexify('[A-Za-z0-9]{24}'),
                'stripe_client_secret' => 'pi_' . fake()->regexify('[A-Za-z0-9]{24}') . '_secret_' . fake()->regexify('[A-Za-z0-9]{24}'),
                'payment_data' => [
                    'stripe_payment_method' => 'card',
                    'last4' => fake()->numerify('####'),
                    'brand' => fake()->randomElement(['visa', 'mastercard', 'amex']),
                    'exp_month' => fake()->numberBetween(1, 12),
                    'exp_year' => fake()->numberBetween(2025, 2030),
                ],
                'metadata' => [
                    'user_agent' => fake()->userAgent(),
                    'ip_address' => fake()->ipv4(),
                    'source' => 'web',
                ],
                'paid_at' => $status === PaymentStatusEnum::SUCCEEDED ? fake()->dateTimeBetween('-30 days', 'now') : null,
                'failed_at' => $status === PaymentStatusEnum::FAILED ? fake()->dateTimeBetween('-30 days', 'now') : null,
                'failure_reason' => $status === PaymentStatusEnum::FAILED ? fake()->randomElement([
                    'Your card was declined.',
                    'Insufficient funds.',
                    'Your card has expired.',
                    'Your card number is incorrect.',
                ]) : null,
            ]);

            // Update order payment status based on payment status
            if ($status === PaymentStatusEnum::SUCCEEDED) {
                $order->markAsPaid();
            }
        }

        $this->command->info('✅ Created ' . $orders->count() . ' sample payment records');

        // Display summary
        $this->command->line('');
        $this->command->line('Payment Status Summary:');
        foreach (PaymentStatusEnum::cases() as $status) {
            $count = Payment::where('status', $status)->count();
            if ($count > 0) {
                $this->command->line("- {$status->label()}: {$count} payments");
            }
        }
    }
}
