<?php
declare(strict_types=1);

namespace App\Test\TestCase\Mailer;

use App\Mailer\BookingMailer;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * Exercises the real templates (email/text/confirmation.php,
 * email/html/confirmation.php), not just the Mailer class in isolation -
 * a wrong template filename crashes silently until send() actually
 * renders, which a test double for the view layer wouldn't catch.
 */
class BookingMailerTest extends TestCase
{
    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Services',
        'app.Customers',
        'app.Bookings',
    ];

    public function testConfirmationSendsWithAnIcsAttachment(): void
    {
        $businesses = TableRegistry::getTableLocator()->get('Businesses');
        $services = TableRegistry::getTableLocator()->get('Services');
        $customers = TableRegistry::getTableLocator()->get('Customers');
        $bookings = TableRegistry::getTableLocator()->get('Bookings');

        $services->behaviors()->get('TenantScope')->setTenantId(1);
        $customers->behaviors()->get('TenantScope')->setTenantId(1);
        $bookings->behaviors()->get('TenantScope')->setTenantId(1);

        $booking = $bookings->get(1);
        $booking->set('business', $businesses->get(1));
        $booking->set('service', $services->get(1));
        $booking->set('customer', $customers->get(1));

        $mailer = new BookingMailer();
        $result = $mailer->send('confirmation', [$booking]);

        $this->assertStringContainsString('To: ', $result['headers']);
        $this->assertStringContainsString('Subject: ', $result['headers']);
        $this->assertStringContainsString('filename="booking.ics"', $result['message']);
        $this->assertStringContainsString('text/calendar', $result['message']);

        // The attachment body is base64-encoded in the raw message; decode
        // the ICS payload out and check it's the real thing, not just that
        // an attachment with the right filename/mimetype exists.
        preg_match('/Content-Transfer-Encoding: base64\r\n\r\n(.+?)\r\n\r\n/s', $result['message'], $matches);
        $decoded = base64_decode(str_replace("\r\n", '', $matches[1]));
        $this->assertStringContainsString('BEGIN:VCALENDAR', $decoded);
        $this->assertStringContainsString('SUMMARY:Haircut at Alpha Hair Studio', $decoded);
    }
}
