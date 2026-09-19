<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * Proves the owner/staff role boundary within a single Business - the
 * brief's other explicit example alongside tenant isolation: "only an
 * owner can edit billing or delete staff".
 */
class RolePermissionBoundaryTest extends TestCase
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
     * @param int $userId The Users.id to log in as - see
     *   AuthorizationBoundaryTest::loginAsUserId() for why session() is used
     *   instead of a real login POST.
     * @return void
     */
    private function loginAsUserId(int $userId): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->find('unscoped')->where(['id' => $userId])->first();
        $this->session(['Auth' => $user->toArray()]);
    }

    public function testStaffCannotEditBusinessBillingDetails(): void
    {
        // User 3 is staff at business 1.
        $this->loginAsUserId(3);

        $this->get('/admin/businesses/edit/1');

        $this->assertResponseCode(403);
    }

    public function testOwnerCanEditBusinessBillingDetails(): void
    {
        // User 1 is owner at business 1.
        $this->loginAsUserId(1);

        $this->get('/admin/businesses/edit/1');

        $this->assertResponseOk();
    }

    public function testStaffCannotDeleteAnotherStaffMember(): void
    {
        // User 3 (staff) tries to delete user 1 (owner), same business.
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->loginAsUserId(3);

        $this->post('/admin/users/delete/1');

        $this->assertResponseCode(403);
    }

    public function testOwnerCanDeleteAStaffMember(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->loginAsUserId(1);

        $this->post('/admin/users/delete/3');

        $this->assertRedirect(['action' => 'index']);

        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId(1);
        $this->assertNull($users->find()->where(['id' => 3])->first());
    }

    public function testStaffCanEditTheirOwnUserRecord(): void
    {
        $this->loginAsUserId(3);

        $this->get('/admin/users/edit/3');

        $this->assertResponseOk();
    }

    public function testStaffCanManageServicesWithinTheirBusiness(): void
    {
        // The brief does not restrict Service management to the owner role.
        $this->loginAsUserId(3);

        $this->get('/admin/services/edit/1');

        $this->assertResponseOk();
    }

    public function testStaffCanManageBookingsWithinTheirBusiness(): void
    {
        $this->loginAsUserId(3);

        $this->get('/admin/bookings/edit/1');

        $this->assertResponseOk();
    }
}
