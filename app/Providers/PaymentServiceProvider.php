<?php

namespace App\Providers;

use App\Services\Payment\Contracts\PaymentServiceInterface;
use App\Services\Payment\StripePaymentService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the default payment service (Stripe)
        $this->app->bind(PaymentServiceInterface::class, StripePaymentService::class);

        // Register specific payment services
        $this->app->bind('payment.stripe', StripePaymentService::class);

        // You can add more payment services here in the future
        // $this->app->bind('payment.paypal', PayPalPaymentService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            PaymentServiceInterface::class,
            'payment.stripe',
        ];
    }
}
