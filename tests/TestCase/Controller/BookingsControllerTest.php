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
}
