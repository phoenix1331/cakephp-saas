<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Proves the Policy classes + TenantScopeBehavior together stop one
 * business's owner from viewing or editing another business's data -
 * the exact failure mode the brief calls out as a cross-tenant data leak.
 */
class AuthorizationBoundaryTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Services',
        'app.Customers',
        'app.Bookings',
    ];

    /**
     * IntegrationTestTrait rebuilds a fresh session for every request from
     * $this->_session, so logging in via post('/admin/users/login') in one
     * call does not carry the identity forward to the next get()/post() -
     * session('Auth' => ...) is the documented way to simulate an existing
     * login for requests after the first.
     *
     * @param int $userId The Users.id to log in as.
     * @return void
     */
    private function loginAsUserId(int $userId): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->find('unscoped')->where(['id' => $userId])->first();
        $this->session(['Auth' => $user->toArray()]);
    }

    public function testOwnerCannotViewAnotherBusinessesService(): void
    {
        // User 2 belongs to business 2; service 1 belongs to business 1.
        $this->loginAsUserId(2);

        $this->get('/admin/services/view/1');

        $this->assertResponseCode(404);
    }

    public function testOwnerCannotEditAnotherBusinessesBooking(): void
    {
        // User 2 belongs to business 2; booking 1 belongs to business 1.
        $this->loginAsUserId(2);

        $this->get('/admin/bookings/edit/1');

        $this->assertResponseCode(404);
    }

    public function testOwnerCanViewTheirOwnService(): void
    {
        // User 1 and service 1 both belong to business 1.
        $this->loginAsUserId(1);

        $this->get('/admin/services/view/1');

        $this->assertResponseOk();
    }

    public function testServicesIndexOnlyShowsTheCurrentBusinessesServices(): void
    {
        $this->loginAsUserId(2);

        $this->get('/admin/services');

        $this->assertResponseOk();
        $this->assertResponseNotContains('Haircut');
        $this->assertResponseContains('Maths Tutoring Session');
    }
}
