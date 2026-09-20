<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Proves the 14-day trial gate (Business::hasAccess(), enforced in
 * Admin\AppController::beforeFilter()) - the brief's "gates access before
 * a card is required".
 */
class TrialGateTest extends TestCase
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

    /**
     * @param int $userId The Users.id to log in as.
     */
    private function loginAsUserId(int $userId): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->find('unscoped')->where(['id' => $userId])->first();
        $this->session(['Auth' => $user->toArray()]);
    }

    private function expireTrial(int $businessId): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get($businessId);
        $businesses->patchEntity($business, ['trial_ends_at' => DateTime::now()->subDays(1)]);
        $businesses->saveOrFail($business, ['checkRules' => false]);
    }

    public function testAWithinTrialBusinessKeepsFullDashboardAccess(): void
    {
        // BusinessesFixture defaults every Business to a week left on trial.
        $this->loginAsUserId(1);

        $this->get('/admin/services');

        $this->assertResponseOk();
    }

    public function testAnExpiredTrialWithNoSubscriptionRedirectsAwayFromTheDashboard(): void
    {
        $this->expireTrial(1);
        $this->loginAsUserId(1);

        $this->get('/admin/services');

        $this->assertRedirect(['controller' => 'Businesses', 'action' => 'view', 1]);
    }

    public function testAnExpiredTrialWithAnActiveSubscriptionKeepsAccess(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $business = $businesses->get(1);
        $businesses->patchEntity($business, [
            'trial_ends_at' => DateTime::now()->subDays(1),
            'subscription_status' => 'active',
        ]);
        $businesses->saveOrFail($business, ['checkRules' => false]);

        $this->loginAsUserId(1);

        $this->get('/admin/services');

        $this->assertResponseOk();
    }

    public function testAGatedOwnerCanStillReachTheirBusinessPageAndCheckout(): void
    {
        $this->expireTrial(1);
        $this->loginAsUserId(1);

        $this->get('/admin/businesses/view/1');

        $this->assertResponseOk();
    }

    public function testAGatedOwnerCanStillLogOut(): void
    {
        $this->expireTrial(1);
        $this->loginAsUserId(1);

        $this->get('/admin/users/logout');

        $this->assertRedirect(['controller' => 'Users', 'action' => 'login']);
    }

    public function testAGatedRequestDoesNotThrowAnAuthorizationRequiredException(): void
    {
        // The gate redirects before any action-specific authorize() call
        // runs, so it must call skipAuthorization() itself - regression
        // test for the 500 this caused when that call was missing.
        $this->expireTrial(1);
        $this->loginAsUserId(1);

        $this->get('/admin/bookings');

        $this->assertResponseCode(302);
    }
}
