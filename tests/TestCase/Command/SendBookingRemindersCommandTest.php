<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class SendBookingRemindersCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Services',
        'app.Customers',
        'app.Bookings',
    ];

    /**
     * @param string $start Booking start, `Y-m-d H:i:s`.
     * @param string $status Booking status.
     * @param string|null $reminderSentAt Existing reminder_sent_at, or null.
     * @param int $businessId Tenant to create the booking under.
     * @return \App\Model\Entity\Booking
     */
    private function createBooking(
        string $start,
        string $status = 'pending',
        ?string $reminderSentAt = null,
        int $businessId = 1,
    ) {
        $bookings = TableRegistry::getTableLocator()->get('Bookings');
        $bookings->behaviors()->get('TenantScope')->setTenantId($businessId);

        $startTime = new DateTime($start);
        $booking = $bookings->newEntity([
            'business_id' => $businessId,
            'service_id' => $businessId === 1 ? 1 : 2,
            'user_id' => $businessId === 1 ? 1 : 2,
            'customer_id' => $businessId === 1 ? 1 : 2,
            'start_time' => $startTime,
            'end_time' => $startTime->addMinutes(30),
            'status' => $status,
            'reminder_sent_at' => $reminderSentAt,
        ]);

        return $bookings->saveOrFail($booking, ['checkRules' => false]);
    }

    public function testSendsAReminderForABookingWithinTheWindow(): void
    {
        $booking = $this->createBooking(DateTime::now()->addHours(2)->format('Y-m-d H:i:s'));

        $this->exec('send_booking_reminders --hours 24');

        $this->assertExitSuccess();
        $this->assertOutputContains('Found 1 booking(s) due a reminder.');
        $this->assertOutputContains('Sent 1 reminder(s).');

        $bookings = TableRegistry::getTableLocator()->get('Bookings');
        $bookings->behaviors()->get('TenantScope')->setTenantId(1);
        $updated = $bookings->get($booking->id);
        $this->assertNotNull($updated->reminder_sent_at);
    }

    public function testDoesNotSendForABookingOutsideTheWindow(): void
    {
        $this->createBooking(DateTime::now()->addHours(48)->format('Y-m-d H:i:s'));

        $this->exec('send_booking_reminders --hours 24');

        $this->assertExitSuccess();
        $this->assertOutputContains('Found 0 booking(s) due a reminder.');
    }

    public function testDoesNotSendForACancelledBooking(): void
    {
        $this->createBooking(DateTime::now()->addHours(2)->format('Y-m-d H:i:s'), status: 'cancelled');

        $this->exec('send_booking_reminders --hours 24');

        $this->assertExitSuccess();
        $this->assertOutputContains('Found 0 booking(s) due a reminder.');
    }

    public function testDoesNotResendWhenReminderAlreadySent(): void
    {
        $this->createBooking(
            DateTime::now()->addHours(2)->format('Y-m-d H:i:s'),
            reminderSentAt: DateTime::now()->format('Y-m-d H:i:s'),
        );

        $this->exec('send_booking_reminders --hours 24');

        $this->assertExitSuccess();
        $this->assertOutputContains('Found 0 booking(s) due a reminder.');
    }

    public function testProcessesBookingsAcrossMultipleTenantsInOneRun(): void
    {
        $this->createBooking(DateTime::now()->addHours(2)->format('Y-m-d H:i:s'), businessId: 1);
        $this->createBooking(DateTime::now()->addHours(3)->format('Y-m-d H:i:s'), businessId: 2);

        $this->exec('send_booking_reminders --hours 24');

        $this->assertExitSuccess();
        $this->assertOutputContains('Found 2 booking(s) due a reminder.');
        $this->assertOutputContains('Sent 2 reminder(s).');
    }

    public function testRunningTwiceDoesNotSendDuplicateReminders(): void
    {
        $this->createBooking(DateTime::now()->addHours(2)->format('Y-m-d H:i:s'));

        $this->exec('send_booking_reminders --hours 24');
        $this->assertOutputContains('Sent 1 reminder(s).');

        $this->exec('send_booking_reminders --hours 24');
        $this->assertOutputContains('Found 0 booking(s) due a reminder.');
    }
}
