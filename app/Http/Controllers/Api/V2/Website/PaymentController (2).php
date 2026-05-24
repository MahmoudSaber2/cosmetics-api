<?php

namespace App\Http\Controllers\Api\V2\Website;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Payment\CreatePaymentIntentRequest;
use App\Http\Requests\V2\Payment\ConfirmPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Payment",
 *     description="Payment processing operations"
 * )
 */
class PaymentController extends Controller
{
    public function __construct(
        private PaymentServiceInterface $paymentService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/v2/website/orders/{order}/payment/create-intent",
     *     summary="Create payment intent",
     *     description="Create a payment intent for an order",
     *     operationId="createPaymentIntent",
     *     tags={"Payment"},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="currency", type="string", example="usd", description="Payment currency"),
     *             @OA\Property(property="payment_method", type="string", example="stripe", description="Payment method")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment intent created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment intent created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="payment_id", type="integer", example=1),
     *                 @OA\Property(property="client_secret", type="string", example="pi_xxx_secret_xxx"),
     *                 @OA\Property(property="amount", type="number", example=99.99),
     *                 @OA\Property(property="currency", type="string", example="usd")
     *             )
     *         )
     *     )
     * )
     */
    public function createPaymentIntent(CreatePaymentIntentRequest $request, Order $order)
    {
        try {
            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $request->payment_method ?? PaymentMethodEnum::STRIPE,
                'status' => PaymentStatusEnum::PENDING,
                'amount' => $order->getPayableAmount(),
                'currency' => $request->currency ?? 'usd',
            ]);

            // Create payment intent with payment service
            $result = $this->paymentService->createPaymentIntent($order, [
                'currency' => $request->currency ?? 'usd',
            ]);

            if (!$result['success']) {
                DB::rollback();
                return ApiResponse::error(
                    __('messages.payment_intent_creation_failed'),
                    ['error' => $result['error']],
                    HttpStatusCode::BAD_REQUEST
                );
            }

            // Update payment with Stripe data
            $payment->update([
                'stripe_payment_intent_id' => $result['payment_intent_id'],
                'stripe_client_secret' => $result['client_secret'],
                'payment_data' => $result,
            ]);

            DB::commit();

            return ApiResponse::success([
                'payment_id' => $payment->id,
                'client_secret' => $result['client_secret'],
                'amount' => $order->getPayableAmount(),
                'currency' => $request->currency ?? 'usd',
            ], __('messages.payment_intent_created'), HttpStatusCode::CREATED);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment Intent Creation Failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                __('messages.error'),
                ['error' => $e->getMessage()],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/website/payments/{payment}/confirm",
     *     summary="Confirm payment",
     *     description="Confirm a payment after client-side processing",
     *     operationId="confirmPayment",
     *     tags={"Payment"},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_intent_id", type="string", example="pi_xxx", description="Stripe payment intent ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment confirmed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment confirmed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="payment_status", type="string", example="succeeded"),
     *                 @OA\Property(property="order_status", type="string", example="approved")
     *             )
     *         )
     *     )
     * )
     */
    public function confirmPayment(ConfirmPaymentRequest $request, Payment $payment)
    {
        try {
            DB::beginTransaction();

            $result = $this->paymentService->confirmPayment($request->payment_intent_id);

            if (!$result['success']) {
                DB::rollback();
                return ApiResponse::error(
                    __('messages.payment_confirmation_failed'),
                    ['error' => $result['error']],
                    HttpStatusCode::BAD_REQUEST
                );
            }

            // Update payment status based on Stripe response
            $stripeStatus = $result['payment_intent']->status;

            if ($stripeStatus === 'succeeded') {
                $payment->markAsSucceeded($result['payment_intent']->id);
                $payment->order->markAsPaid();

                DB::commit();

                return ApiResponse::success([
                    'payment_status' => 'succeeded',
                    'order_status' => 'approved',
                ], __('messages.payment_confirmed'));
            } else {
                $payment->status = PaymentStatusEnum::from($this->paymentService->getPaymentStatus($request->payment_intent_id));
                $payment->save();

                DB::commit();

                return ApiResponse::success([
                    'payment_status' => $stripeStatus,
                    'order_status' => $payment->order->status->value,
                ], __('messages.payment_status_updated'));
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment Confirmation Failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error(
                __('messages.error'),
                ['error' => $e->getMessage()],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/website/payments/webhook/stripe",
     *     summary="Handle Stripe webhook",
     *     description="Handle Stripe webhook events",
     *     operationId="handleStripeWebhook",
     *     tags={"Payment"},
     *     @OA\Response(
     *         response=200,
     *         description="Webhook handled successfully"
     *     )
     * )
     */
    public function handleStripeWebhook(Request $request)
    {
        try {
            $payload = $request->all();
            $result = $this->paymentService->handleWebhook($payload);

            if ($result) {
                return response()->json(['status' => 'success'], 200);
            }

            return response()->json(['status' => 'error'], 400);

        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v2/website/payments/{payment}/status",
     *     summary="Get payment status",
     *     description="Get the current status of a payment",
     *     operationId="getPaymentStatus",
     *     tags={"Payment"},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment status retrieved"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="payment_status", type="string", example="succeeded"),
     *                 @OA\Property(property="order_status", type="string", example="approved"),
     *                 @OA\Property(property="amount", type="number", example=99.99),
     *                 @OA\Property(property="currency", type="string", example="usd")
     *             )
     *         )
     *     )
     * )
     */
    public function getPaymentStatus(Payment $payment)
    {
        return ApiResponse::success([
            'payment_status' => $payment->status->value,
            'order_status' => $payment->order->status->value,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'paid_at' => $payment->paid_at,
            'failed_at' => $payment->failed_at,
            'failure_reason' => $payment->failure_reason,
        ], __('messages.payment_status_retrieved'));
    }
}
