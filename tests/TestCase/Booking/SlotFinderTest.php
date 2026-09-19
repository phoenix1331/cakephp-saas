<?php
declare(strict_types=1);

namespace App\Test\TestCase\Booking;

use App\Booking\SlotFinder;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\TestSuite\TestCase;

class SlotFinderTest extends TestCase
{
    protected array $fixtures = [
        'app.Businesses',
        'app.Users',
        'app.Services',
        'app.Availabilities',
        'app.Customers',
        'app.Bookings',
    ];

    /**
     * @param array<\Cake\I18n\Time> $slots Slots as returned by SlotFinder.
     * @return array<string> The same slots as 24-hour `H:i` strings.
     */
    private function formatSlots(array $slots): array
    {
        return array_map(static fn(Time $slot): string => $slot->format('H:i'), $slots);
    }

    public function testFindsSlotsAcrossARecurringAvailabilityWindow(): void
    {
        $finder = new SlotFinder();

        // User 1, recurring Monday 09:00-17:00, no existing bookings that day.
        $slots = $this->formatSlots($finder->findSlots(userId: 1, durationMinutes: 30, date: new Date('2026-02-02')));

        $this->assertSame('09:00', $slots[0]);
        $this->assertSame('16:30', end($slots));
        $this->assertCount(16, $slots);
    }

    public function testReturnsNoSlotsOnADayWithNoAvailabilityRule(): void
    {
        $finder = new SlotFinder();

        // User 1 has no Wednesday rule at all.
        $slots = $finder->findSlots(userId: 1, durationMinutes: 30, date: new Date('2026-02-04'));

        $this->assertSame([], $slots);
    }

    public function testDateOverrideTakesPrecedenceOverRecurringRule(): void
    {
        $finder = new SlotFinder();

        // User 1's recurring rule is Monday-only, but 2026-02-03 (Tuesday)
        // has an explicit override: 09:00-12:00.
        $slots = $this->formatSlots($finder->findSlots(userId: 1, durationMinutes: 60, date: new Date('2026-02-03')));

        $this->assertSame(['09:00', '10:00', '11:00'], $slots);
    }

    public function testUnavailableOverrideBeatsAnOtherwiseAvailableRecurringDay(): void
    {
        $finder = new SlotFinder();

        // User 2's recurring rule says Monday 09:00-17:00, but an override
        // on 2026-02-02 explicitly marks that day unavailable.
        $slots = $finder->findSlots(userId: 2, durationMinutes: 30, date: new Date('2026-02-02'));

        $this->assertSame([], $slots);
    }

    /**
     * @param int $userId The staff member.
     * @param string $start Booking start, `Y-m-d H:i:s`.
     * @param string $end Booking end, `Y-m-d H:i:s`.
     * @param string $status Booking status.
     * @return void
     */
    private function createBooking(int $userId, string $start, string $end, string $status = 'confirmed'): void
    {
        $tables = ['Bookings', 'Services', 'Customers', 'Users'];
        foreach ($tables as $alias) {
            $this->getTableLocator()->get($alias)->behaviors()->get('TenantScope')->setTenantId(1);
        }

        $bookings = $this->getTableLocator()->get('Bookings');
        $bookings->saveOrFail($bookings->newEntity([
            'business_id' => 1,
            'service_id' => 1,
            'user_id' => $userId,
            'customer_id' => 1,
            'start_time' => $start,
            'end_time' => $end,
            'status' => $status,
        ]));
    }

    public function testExistingBookingRemovesOverlappingSlots(): void
    {
        $this->createBooking(1, '2026-02-02 10:00:00', '2026-02-02 10:30:00');

        // No buffer, so only the exact 10:00 slot should disappear.
        $finder = new SlotFinder(bufferMinutes: 0);
        $slots = $this->formatSlots($finder->findSlots(userId: 1, durationMinutes: 30, date: new Date('2026-02-02')));

        $this->assertNotContains('10:00', $slots);
        $this->assertContains('09:30', $slots);
        $this->assertContains('10:30', $slots);
    }

    public function testBufferExtendsBlockedRangeBeforeAndAfterABooking(): void
    {
        $this->createBooking(1, '2026-02-02 10:00:00', '2026-02-02 10:30:00');

        // 15 minute buffer either side of the 10:00-10:30 booking blocks
        // 09:45-10:45, so both the 09:30 and 10:30 slots must also go.
        $finder = new SlotFinder(bufferMinutes: 15);
        $slots = $this->formatSlots($finder->findSlots(userId: 1, durationMinutes: 30, date: new Date('2026-02-02')));

        $this->assertNotContains('09:30', $slots);
        $this->assertNotContains('10:00', $slots);
        $this->assertNotContains('10:30', $slots);
        $this->assertContains('09:00', $slots);
        $this->assertContains('11:00', $slots);
    }

    public function testCancelledBookingsDoNotBlockSlots(): void
    {
        $this->createBooking(1, '2026-02-02 10:00:00', '2026-02-02 10:30:00', 'cancelled');

        $finder = new SlotFinder(bufferMinutes: 0);
        $slots = $this->formatSlots($finder->findSlots(userId: 1, durationMinutes: 30, date: new Date('2026-02-02')));

        $this->assertContains('10:00', $slots);
    }

    public function testSlotsFromOneStaffMemberDoNotLeakToAnother(): void
    {
        $this->createBooking(1, '2026-02-03 09:00:00', '2026-02-03 10:00:00');

        // User 2 has no availability at all on 2026-02-03, so this also
        // doubles as a check that user 1's booking doesn't leak into a
        // completely different (and here, empty) result for user 2.
        $finder = new SlotFinder();
        $slots = $finder->findSlots(userId: 2, durationMinutes: 60, date: new Date('2026-02-03'));

        $this->assertSame([], $slots);
    }
}
