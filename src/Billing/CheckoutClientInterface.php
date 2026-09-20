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

    /**
     * @param string $customerId Stripe Customer id.
     * @param string $returnUrl Redirect URL once the customer leaves the portal.
     * @return string The Billing Portal session's hosted page URL.
     */
    public function createBillingPortalSessionUrl(string $customerId, string $returnUrl): string;

    /**
     * Looks up the Stripe Price id a Subscription is billed against. A
     * Checkout Session's subscription field is an unexpanded id on the
     * webhook payload, so the price has to be fetched separately - used
     * by StripeWebhooksController to resolve which local Plan a completed
     * checkout corresponds to.
     *
     * @param string $subscriptionId Stripe Subscription id.
     * @return string|null The subscription's Price id, or null if it has no items.
     */
    public function getSubscriptionPriceId(string $subscriptionId): ?string;
}
