<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

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
}
