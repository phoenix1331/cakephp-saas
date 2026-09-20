<?php
declare(strict_types=1);

namespace App\Billing;

/**
 * Builds the CheckoutClientInterface implementation BusinessesController
 * uses. Real requests get StripeCheckoutClient; tests call setForTesting()
 * with a fake to exercise the controller/policy/persistence logic without
 * making real Stripe API calls - the same swap-in-a-static-instance
 * pattern CakePHP itself uses for Cache/Log engines.
 */
class StripeClientFactory
{
    private static ?CheckoutClientInterface $instance = null;

    /**
     * @return \App\Billing\CheckoutClientInterface
     */
    public static function create(): CheckoutClientInterface
    {
        return self::$instance ??= new StripeCheckoutClient();
    }

    /**
     * @param \App\Billing\CheckoutClientInterface|null $client A fake client, or null to reset to the real one.
     * @return void
     */
    public static function setForTesting(?CheckoutClientInterface $client): void
    {
        self::$instance = $client;
    }
}
