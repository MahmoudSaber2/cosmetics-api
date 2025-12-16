<?php

namespace App\Services\Payment;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentServiceInterface;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService implements PaymentServiceInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent
     */
    public function createPaymentIntent(Order $order, array $options = []): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $this->convertToStripeAmount($order->getPayableAmount()),
                'currency' => $options['currency'] ?? 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->number,
                    'client_id' => $order->client_id,
                ],
                'description' => "Payment for Order #{$order->number}",
            ]);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent Creation Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process payment
     */
    public function processPayment(Payment $payment, array $paymentData): bool
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($payment->stripe_payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $payment->markAsSucceeded($paymentIntent->id);
                $payment->order->markAsPaid();
                return true;
            }

            return false;
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Processing Failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            $payment->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Confirm payment
     */
    public function confirmPayment(string $paymentIntentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'payment_intent' => $paymentIntent,
            ];
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Confirmation Failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(Payment $payment, ?float $amount = null): bool
    {
        try {
            $refundData = [
                'payment_intent' => $payment->stripe_payment_intent_id,
            ];

            if ($amount) {
                $refundData['amount'] = $this->convertToStripeAmount($amount);
            }

            $refund = Refund::create($refundData);

            if ($refund->status === 'succeeded') {
                $payment->status = PaymentStatusEnum::REFUNDED;
                $payment->save();
                return true;
            }

            return false;
        } catch (ApiErrorException $e) {
            Log::error('Stripe Refund Failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentIntentId): string
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            return $this->mapStripeStatusToEnum($paymentIntent->status);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Status Retrieval Failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            return PaymentStatusEnum::FAILED->value;
        }
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): bool
    {
        try {
            $event = Webhook::constructEvent(
                json_encode($payload),
                request()->header('Stripe-Signature'),
                config('services.stripe.webhook_secret')
            );

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
                default:
                    Log::info('Unhandled Stripe webhook event', ['type' => $event->type]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Handling Failed', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }
    }

    /**
     * Convert amount to Stripe format (cents)
     */
    private function convertToStripeAmount(float $amount): int
    {
        return (int) ($amount * 100);
    }



    /**
     * Map Stripe status to our enum
     */
    private function mapStripeStatusToEnum(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'requires_payment_method', 'requires_confirmation' => PaymentStatusEnum::PENDING->value,
            'processing' => PaymentStatusEnum::PROCESSING->value,
            'succeeded' => PaymentStatusEnum::SUCCEEDED->value,
            'canceled' => PaymentStatusEnum::CANCELED->value,
            default => PaymentStatusEnum::FAILED->value,
        };
    }

    /**
     * Handle successful payment webhook
     */
    private function handlePaymentSucceeded($paymentIntent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->markAsSucceeded($paymentIntent->id);
            $payment->order->markAsPaid();
        }
    }

    /**
     * Handle failed payment webhook
     */
    private function handlePaymentFailed($paymentIntent): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if ($payment) {
            $payment->markAsFailed($paymentIntent->last_payment_error->message ?? 'Payment failed');

            // Delete order and restore inventory if payment failed
            $this->handleFailedPayment($payment->order);
        }
    }

    /**
     * Handle failed payment - delete order and restore inventory
     */
    private function handleFailedPayment(Order $order): void
    {
        // Restore inventory for products with stock
        foreach ($order->orderItems as $orderItem) {
            if ($orderItem->product->has_stock && $orderItem->product->inventory) {
                $orderItem->product->inventory->increaseStock($orderItem->quantity);
            }
        }

        // Soft delete the order
        $order->delete();
    }
}
