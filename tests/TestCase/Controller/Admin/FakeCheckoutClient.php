<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Billing\CheckoutClientInterface;
use RuntimeException;

/**
 * A CheckoutClientInterface fake for BusinessesControllerTest - makes no
 * network calls, returns canned values, and records what it was called
 * with so tests can assert on it.
 */
class FakeCheckoutClient implements CheckoutClientInterface
{
    public ?string $lastCustomerName = null;

    public ?int $lastBusinessId = null;

    public ?string $lastPriceId = null;

    public ?string $lastPortalCustomerId = null;

    public bool $shouldFailSession = false;

    public bool $shouldFailPortalSession = false;

    public function createCustomer(string $name, int $businessId): string
    {
        $this->lastCustomerName = $name;
        $this->lastBusinessId = $businessId;

        return 'cus_fake_123';
    }

    public function createCheckoutSessionUrl(
        string $customerId,
        string $priceId,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $this->lastPriceId = $priceId;

        if ($this->shouldFailSession) {
            throw new RuntimeException('stripe is down');
        }

        return 'https://checkout.stripe.com/c/pay/cs_fake_123';
    }

    public function createBillingPortalSessionUrl(string $customerId, string $returnUrl): string
    {
        $this->lastPortalCustomerId = $customerId;

        if ($this->shouldFailPortalSession) {
            throw new RuntimeException('stripe is down');
        }

        return 'https://billing.stripe.com/p/session/bps_fake_123';
    }
}
