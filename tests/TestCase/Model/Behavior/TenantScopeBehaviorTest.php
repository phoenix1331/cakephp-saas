<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use RuntimeException;

class TenantScopeBehaviorTest extends TestCase
{
    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Services',
        'app.Customers',
        'app.Bookings',
    ];

    public function testFindIsScopedToCurrentTenant(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId(1);

        $results = $users->find()->all();

        $this->assertCount(1, $results);
        $this->assertSame(1, $results->first()->business_id);
    }

    public function testFindDoesNotLeakOtherTenantsRows(): void
    {
        $bookings = TableRegistry::getTableLocator()->get('Bookings');
        $bookings->behaviors()->get('TenantScope')->setTenantId(1);

        $results = $bookings->find()->all();

        foreach ($results as $booking) {
            $this->assertSame(1, $booking->business_id);
        }
        $this->assertCount(1, $results);
    }

    public function testFindWithoutTenantIdThrows(): void
    {
        $customers = TableRegistry::getTableLocator()->get('Customers');

        $this->expectException(RuntimeException::class);
        $customers->find()->all();
    }

    public function testSaveStampsTenantIdOnNewEntity(): void
    {
        $services = TableRegistry::getTableLocator()->get('Services');
        $services->behaviors()->get('TenantScope')->setTenantId(1);

        $service = $services->newEntity([
            'name' => 'Beard Trim',
            'duration_minutes' => 15,
            'price' => '10.00',
        ]);
        $saved = $services->save($service);

        $this->assertNotFalse($saved);
        $this->assertSame(1, $saved->business_id);
    }

    public function testSaveRefusesCrossTenantEntity(): void
    {
        $services = TableRegistry::getTableLocator()->get('Services');
        $services->behaviors()->get('TenantScope')->setTenantId(1);

        $service = $services->newEntity([
            'business_id' => 2,
            'name' => 'Cross-tenant attempt',
            'duration_minutes' => 15,
            'price' => '10.00',
        ]);

        $this->expectException(RuntimeException::class);
        $services->save($service);
    }

    public function testSaveWithoutTenantIdThrows(): void
    {
        $customers = TableRegistry::getTableLocator()->get('Customers');
        $customer = $customers->newEntity([
            'business_id' => 1,
            'name' => 'New Customer',
            'email' => 'new-customer@example.test',
        ]);

        $this->expectException(RuntimeException::class);
        $customers->save($customer);
    }

    public function testFindUnscopedBypassesTheTenantFilterEvenWithoutTenantId(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');

        $results = $users->find('unscoped')->all();

        $this->assertCount(2, $results);
    }

    public function testFindUnscopedIsOnlyTheExplicitBypassNotTheDefault(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $users->behaviors()->get('TenantScope')->setTenantId(1);

        $scoped = $users->find()->all();
        $unscoped = $users->find('unscoped')->all();

        $this->assertCount(1, $scoped);
        $this->assertCount(2, $unscoped);
    }
}
