<?php
declare(strict_types=1);

namespace App\Billing;

use Cake\Core\Configure;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * The real, Stripe-backed CheckoutClientInterface implementation.
 */
class StripeCheckoutClient implements CheckoutClientInterface
{
    private StripeClient $stripe;

    /**
     * @throws \RuntimeException When `Stripe.secretKey` is not configured.
     */
    public function __construct()
    {
        $secretKey = Configure::read('Stripe.secretKey');

        if (empty($secretKey)) {
            throw new RuntimeException(
                'Missing `Stripe.secretKey` configuration. Set STRIPE_SECRET_KEY in the environment.',
            );
        }

        $this->stripe = new StripeClient($secretKey);
    }

    /**
     * @inheritDoc
     */
    public function createCustomer(string $name, int $businessId): string
    {
        $customer = $this->stripe->customers->create([
            'name' => $name,
            'metadata' => ['business_id' => $businessId],
        ]);

        return $customer->id;
    }

    /**
     * @inheritDoc
     */
    public function createCheckoutSessionUrl(
        string $customerId,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
    ): string {
        try {
            $session = $this->stripe->checkout->sessions->create([
                'customer' => $customerId,
                'mode' => 'subscription',
                'line_items' => [
                    ['price' => $priceId, 'quantity' => 1],
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);
        } catch (ApiErrorException $exception) {
            throw new RuntimeException($exception->getMessage(), previous: $exception);
        }

        return $session->url;
    }
}
