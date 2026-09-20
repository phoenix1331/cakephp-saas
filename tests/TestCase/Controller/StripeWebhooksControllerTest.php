<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Billing\StripeClientFactory;
use App\Test\TestCase\Billing\FakeCheckoutClient;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Stripe's webhook signature is an HMAC-SHA256 of "{timestamp}.{payload}"
 * keyed with the webhook secret (Stripe\WebhookSignature::computeSignature())
 * - these tests replicate that directly rather than mocking Stripe\Webhook,
 * so a real signature-verification bug would actually be caught.
 */
class StripeWebhooksControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Businesses',
        'app.Plans',
    ];

    private const WEBHOOK_SECRET = 'whsec_test_secret';

    private FakeCheckoutClient $checkoutClient;

    public function setUp(): void
    {
        parent::setUp();

        Configure::write('Stripe.webhookSecret', self::WEBHOOK_SECRET);

        $this->checkoutClient = new FakeCheckoutClient();
        StripeClientFactory::setForTesting($this->checkoutClient);
    }

    public function tearDown(): void
    {
        StripeClientFactory::setForTesting(null);

        parent::tearDown();
    }

    /**
     * @param string $payload Raw JSON request body.
     * @return string A valid Stripe-Signature header for that payload.
     */
    private function signedHeader(string $payload): string
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, self::WEBHOOK_SECRET);

        return "t={$timestamp},v1={$signature}";
    }

    /**
     * @param string $type Stripe event type, e.g. `checkout.session.completed`.
     * @param string $objectType The Stripe object type, e.g. `checkout.session` -
     *   Stripe\Util\Util::convertToStripeObject() only resolves data.object to
     *   the correct typed class (Checkout\Session, Subscription, ...) when
     *   this field is present, the same as a real webhook payload always has.
     * @param array<string, mixed> $object The event's `data.object`, minus `object`.
     * @return string
     */
    private function eventPayload(string $type, string $objectType, array $object): string
    {
        return json_encode([
            'id' => 'evt_test_123',
            'type' => $type,
            'data' => ['object' => ['object' => $objectType] + $object],
        ]);
    }

    private function postWebhook(string $payload): void
    {
        $this->configRequest(['headers' => ['Stripe-Signature' => $this->signedHeader($payload)]]);
        $this->post('/webhooks/stripe', $payload);
    }

    public function testRejectsAPayloadWithAnInvalidSignature(): void
    {
        $payload = $this->eventPayload('checkout.session.completed', 'checkout.session', [
            'mode' => 'subscription',
            'customer' => 'cus_1',
            'subscription' => 'sub_1',
        ]);
        $this->configRequest(['headers' => ['Stripe-Signature' => 't=' . time() . ',v1=not-the-real-signature']]);

        $this->post('/webhooks/stripe', $payload);

        $this->assertResponseCode(400);
    }

    public function testRejectsAGetRequest(): void
    {
        $this->get('/webhooks/stripe');

        $this->assertResponseCode(405);
    }

    public function testCheckoutSessionCompletedActivatesTheSubscriptionAndSetsThePlan(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, ['stripe_customer_id' => 'cus_1']);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $this->checkoutClient->subscriptionPriceIds['sub_1'] = 'price_solo_test';

        $payload = $this->eventPayload('checkout.session.completed', 'checkout.session', [
            'mode' => 'subscription',
            'customer' => 'cus_1',
            'subscription' => 'sub_1',
        ]);

        $this->postWebhook($payload);

        $this->assertResponseOk();

        $business = $businesses->get(1);
        $this->assertSame('active', $business->subscription_status);
        $this->assertSame(1, $business->plan_id);
    }

    public function testCheckoutSessionCompletedIgnoresAnUnknownCustomer(): void
    {
        $payload = $this->eventPayload('checkout.session.completed', 'checkout.session', [
            'mode' => 'subscription',
            'customer' => 'cus_does_not_exist',
            'subscription' => 'sub_1',
        ]);

        $this->postWebhook($payload);

        $this->assertResponseOk();
    }

    public function testCheckoutSessionCompletedIgnoresNonSubscriptionModeSessions(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, ['stripe_customer_id' => 'cus_1']);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $payload = $this->eventPayload('checkout.session.completed', 'checkout.session', [
            'mode' => 'payment',
            'customer' => 'cus_1',
            'subscription' => null,
        ]);

        $this->postWebhook($payload);

        $this->assertResponseOk();

        $business = $businesses->get(1);
        $this->assertNull($business->subscription_status);
    }

    public function testSubscriptionUpdatedSyncsTheStatus(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, [
            'stripe_customer_id' => 'cus_1',
            'subscription_status' => 'active',
        ]);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $payload = $this->eventPayload('customer.subscription.updated', 'subscription', [
            'id' => 'sub_1',
            'customer' => 'cus_1',
            'status' => 'past_due',
        ]);

        $this->postWebhook($payload);

        $this->assertResponseOk();

        $business = $businesses->get(1);
        $this->assertSame('past_due', $business->subscription_status);
    }

    public function testSubscriptionDeletedSyncsTheStatusToCanceled(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, [
            'stripe_customer_id' => 'cus_1',
            'subscription_status' => 'active',
        ]);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $payload = $this->eventPayload('customer.subscription.deleted', 'subscription', [
            'id' => 'sub_1',
            'customer' => 'cus_1',
            'status' => 'canceled',
        ]);

        $this->postWebhook($payload);

        $this->assertResponseOk();

        $business = $businesses->get(1);
        $this->assertSame('canceled', $business->subscription_status);
    }

    public function testAnUnhandledEventTypeIsAcceptedAndIgnored(): void
    {
        $payload = $this->eventPayload('invoice.paid', 'invoice', ['id' => 'in_1']);

        $this->postWebhook($payload);

        $this->assertResponseOk();
    }
}
