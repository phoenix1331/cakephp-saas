<?php
declare(strict_types=1);

namespace App\Billing;

/**
 * The narrow slice of the Stripe API BusinessesController::checkout() needs
 * - not a general-purpose Stripe wrapper. Exists so tests can substitute a
 * fake and exercise the controller/policy/persistence logic without making
 * real network calls; StripeCheckoutClient is the real implementation.
 */
interface CheckoutClientInterface
{
    /**
     * @param string $name Customer name.
     * @param int $businessId Business id, stored as Stripe Customer metadata.
     * @return string The created Stripe Customer id.
     */
    public function createCustomer(string $name, int $businessId): string;

    /**
     * @param string $customerId Stripe Customer id.
     * @param string $priceId Stripe Price id.
     * @param string $successUrl Redirect URL on successful checkout.
     * @param string $cancelUrl Redirect URL on cancelled checkout.
     * @return string The Checkout Session's hosted page URL.
     */
    public function createCheckoutSessionUrl(
        string $customerId,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
    ): string;
}
