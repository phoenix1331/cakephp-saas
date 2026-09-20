<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Billing\StripeClientFactory;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class BusinessesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Plans',
        'app.Services',
        'app.Customers',
        'app.Bookings',
    ];

    private FakeCheckoutClient $checkoutClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->checkoutClient = new FakeCheckoutClient();
        StripeClientFactory::setForTesting($this->checkoutClient);
    }

    public function tearDown(): void
    {
        StripeClientFactory::setForTesting(null);

        parent::tearDown();
    }

    /**
     * @param int $userId The Users.id to log in as.
     */
    private function loginAsUserId(int $userId): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->find('unscoped')->where(['id' => $userId])->first();
        $this->session(['Auth' => $user->toArray()]);
    }

    public function testCheckoutCreatesAStripeCustomerOnFirstSubscribeAndRedirectsToTheHostedPage(): void
    {
        $this->loginAsUserId(1);

        $this->get('/admin/businesses/checkout/1/1');

        $this->assertRedirect('https://checkout.stripe.com/c/pay/cs_fake_123');
        $this->assertSame('Alpha Hair Studio', $this->checkoutClient->lastCustomerName);
        $this->assertSame(1, $this->checkoutClient->lastBusinessId);
        $this->assertSame('price_solo_test', $this->checkoutClient->lastPriceId);

        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $this->assertSame('cus_fake_123', $business->stripe_customer_id);
    }

    public function testCheckoutReusesAnExistingStripeCustomerIdRatherThanCreatingAnother(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, ['stripe_customer_id' => 'cus_already_exists']);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $this->loginAsUserId(1);

        $this->get('/admin/businesses/checkout/1/1');

        $this->assertRedirect('https://checkout.stripe.com/c/pay/cs_fake_123');
        $this->assertNull($this->checkoutClient->lastCustomerName);
    }

    public function testCheckoutReturns404ForAPlanWithNoStripePriceConfigured(): void
    {
        $this->loginAsUserId(1);

        $this->get('/admin/businesses/checkout/1/2');

        $this->assertResponseCode(404);
    }

    public function testCheckoutFlashesAnErrorAndRedirectsBackWhenStripeFails(): void
    {
        $this->checkoutClient->shouldFailSession = true;
        $this->loginAsUserId(1);

        $this->get('/admin/businesses/checkout/1/1');

        $this->assertRedirect(['action' => 'view', 1]);
        $this->assertFlashMessage('Could not start checkout: stripe is down');
    }

    public function testOwnerCannotStartCheckoutForAnotherBusiness(): void
    {
        // User 2 belongs to business 2; checking out business 1. Unlike
        // the tenant-scoped tables, Business has no business_id condition
        // to fail the find - BusinessPolicy::canCheckout()'s ownership
        // check is the only thing stopping this, hence 403 not 404.
        $this->loginAsUserId(2);

        $this->get('/admin/businesses/checkout/1/1');

        $this->assertResponseCode(403);
    }

    public function testStaffCannotStartCheckout(): void
    {
        // User 3 is staff, not owner, at business 1.
        $this->loginAsUserId(3);

        $this->get('/admin/businesses/checkout/1/1');

        $this->assertResponseCode(403);
    }

    public function testBillingPortalRedirectsToTheHostedPageForAnExistingCustomer(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, ['stripe_customer_id' => 'cus_already_exists']);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $this->loginAsUserId(1);

        $this->get('/admin/businesses/billing-portal/1');

        $this->assertRedirect('https://billing.stripe.com/p/session/bps_fake_123');
        $this->assertSame('cus_already_exists', $this->checkoutClient->lastPortalCustomerId);
    }

    public function testBillingPortalReturns404ForABusinessWithNoStripeCustomerYet(): void
    {
        $this->loginAsUserId(1);

        $this->get('/admin/businesses/billing-portal/1');

        $this->assertResponseCode(404);
    }

    public function testBillingPortalFlashesAnErrorAndRedirectsBackWhenStripeFails(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, ['stripe_customer_id' => 'cus_already_exists']);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $this->checkoutClient->shouldFailPortalSession = true;
        $this->loginAsUserId(1);

        $this->get('/admin/businesses/billing-portal/1');

        $this->assertRedirect(['action' => 'view', 1]);
        $this->assertFlashMessage('Could not open the billing portal: stripe is down');
    }

    public function testStaffCannotOpenTheBillingPortal(): void
    {
        // User 3 is staff, not owner, at business 1.
        $this->loginAsUserId(3);

        $this->get('/admin/businesses/billing-portal/1');

        $this->assertResponseCode(403);
    }
}
