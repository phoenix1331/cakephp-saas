<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class BookingsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Services',
        'app.ServicesUsers',
        'app.Availabilities',
        'app.Customers',
        'app.Bookings',
    ];

    public function testValidSlugResolvesTheBusinessWithoutLogin(): void
    {
        $this->get('/book/alpha-hair-studio');

        $this->assertResponseOk();
        $this->assertResponseContains('Alpha Hair Studio');
    }

    public function testUnknownSlugReturns404(): void
    {
        $this->get('/book/no-such-business');

        $this->assertResponseCode(404);
    }

    public function testOneBusinessesSlugDoesNotResolveAnothers(): void
    {
        $this->get('/book/alpha-hair-studio');

        $this->assertResponseNotContains('Beta Tutoring');
    }

    public function testIndexListsOnlyThatBusinessesServices(): void
    {
        $this->get('/book/alpha-hair-studio');

        $this->assertResponseContains('Haircut');
        $this->assertResponseNotContains('Maths Tutoring Session');
    }

    public function testServicePageShowsAssignedStaff(): void
    {
        $this->get('/book/alpha-hair-studio/service/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Haircut');
        $this->assertResponseContains('owner@alpha-hair-studio.test');
    }

    public function testServicePageFromAnotherBusinessesSlugReturns404(): void
    {
        // Service 1 belongs to business 1 (alpha-hair-studio), not business 2.
        $this->get('/book/beta-tutoring/service/1');

        $this->assertResponseCode(404);
    }

    public function testUnknownServiceIdReturns404(): void
    {
        $this->get('/book/alpha-hair-studio/service/999');

        $this->assertResponseCode(404);
    }

    public function testBookPageShowsSlotsForTheRequestedDate(): void
    {
        // User 1 (service 1's assigned staff) has a recurring Monday
        // 09:00-17:00 availability fixture; 2026-02-02 is a Monday.
        $this->get('/book/alpha-hair-studio/service/1/staff/1?date=2026-02-02');

        $this->assertResponseOk();
        $this->assertResponseContains('value="09:00"');
    }

    public function testBookPageForStaffNotOfferingServiceReturns404(): void
    {
        // Service 1 is only assigned to user 1, not user 2.
        $this->get('/book/alpha-hair-studio/service/1/staff/2?date=2026-02-02');

        $this->assertResponseCode(404);
    }

    public function testSubmittingABookingCreatesItAndMatchesCustomerByEmail(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/book/alpha-hair-studio/service/1/staff/1?date=2026-02-02', [
            'slot' => '09:00',
            'name' => 'Jane Guest',
            'email' => 'jane.guest@example.test',
            'phone' => '07700900000',
        ]);

        $this->assertRedirectContains('/book/alpha-hair-studio');

        $bookings = TableRegistry::getTableLocator()->get('Bookings');
        $bookings->behaviors()->get('TenantScope')->setTenantId(1);
        $booking = $bookings->find()
            ->where(['service_id' => 1, 'user_id' => 1, 'start_time' => '2026-02-02 09:00:00'])
            ->contain(['Customers'])
            ->first();

        $this->assertNotNull($booking);
        $this->assertSame('jane.guest@example.test', $booking->customer->email);
    }

    public function testSecondBookingWithSameEmailReusesTheSameCustomer(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/book/alpha-hair-studio/service/1/staff/1?date=2026-02-02', [
            'slot' => '09:00',
            'name' => 'Jane Guest',
            'email' => 'jane.guest@example.test',
            'phone' => '',
        ]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/book/alpha-hair-studio/service/1/staff/1?date=2026-02-02', [
            'slot' => '10:00',
            'name' => 'Jane Guest',
            'email' => 'jane.guest@example.test',
            'phone' => '',
        ]);

        $customers = TableRegistry::getTableLocator()->get('Customers');
        $customers->behaviors()->get('TenantScope')->setTenantId(1);
        $count = $customers->find()->where(['email' => 'jane.guest@example.test'])->count();

        $this->assertSame(1, $count);
    }

    public function testCannotDoubleBookTheSameSlot(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/book/alpha-hair-studio/service/1/staff/1?date=2026-02-02', [
            'slot' => '09:00',
            'name' => 'First Guest',
            'email' => 'first@example.test',
            'phone' => '',
        ]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/book/alpha-hair-studio/service/1/staff/1?date=2026-02-02', [
            'slot' => '09:00',
            'name' => 'Second Guest',
            'email' => 'second@example.test',
            'phone' => '',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('no longer available');

        $bookings = TableRegistry::getTableLocator()->get('Bookings');
        $bookings->behaviors()->get('TenantScope')->setTenantId(1);
        $count = $bookings->find()
            ->where(['service_id' => 1, 'user_id' => 1, 'start_time' => '2026-02-02 09:00:00'])
            ->count();

        $this->assertSame(1, $count);
    }
}
