<?php

namespace App\Services\Payment\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentServiceInterface
{
    /**
     * Create a payment intent
     */
    public function createPaymentIntent(Order $order, array $options = []): array;

    /**
     * Process payment
     */
    public function processPayment(Payment $payment, array $paymentData): bool;

    /**
     * Confirm payment
     */
    public function confirmPayment(string $paymentIntentId): array;

    /**
     * Refund payment
     */
    public function refundPayment(Payment $payment, ?float $amount = null): bool;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentIntentId): string;

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): bool;
}
