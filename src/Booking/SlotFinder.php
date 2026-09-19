<?php
declare(strict_types=1);

namespace App\Booking;

use Cake\I18n\Date;
use Cake\I18n\DateTime;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;

/**
 * Computes bookable slots for a staff member on a given date: their
 * Availability window for that date, minus existing Bookings, minus a
 * buffer between appointments.
 *
 * Deliberately a plain class, not a Table/Behavior - this is computation
 * over already-persisted data, not persistence itself.
 */
class SlotFinder
{
    /**
     * @param int $bufferMinutes Minutes of gap required before and after
     *   every existing booking. Configurable per the brief - not a fixed
     *   platform-wide constant.
     */
    public function __construct(
        protected int $bufferMinutes = 10,
    ) {
    }

    /**
     * Returns the bookable slot start times for a staff member performing a
     * service of the given duration, on the given date.
     *
     * @param int $userId The staff member (Users.id).
     * @param int $durationMinutes The service duration.
     * @param \Cake\I18n\Date $date The date to compute slots for.
     * @return array<\Cake\I18n\Time>
     */
    public function findSlots(int $userId, int $durationMinutes, Date $date): array
    {
        $window = $this->resolveAvailabilityWindow($userId, $date);
        if ($window === null) {
            return [];
        }

        [$windowStartMinutes, $windowEndMinutes] = $window;
        $busyRanges = $this->resolveBusyRanges($userId, $date);

        $slots = [];
        for (
            $slotStart = $windowStartMinutes;
            $slotStart + $durationMinutes <= $windowEndMinutes;
            $slotStart += $durationMinutes
        ) {
            $slotEnd = $slotStart + $durationMinutes;

            if (!$this->overlapsAnyBusyRange($slotStart, $slotEnd, $busyRanges)) {
                $slots[] = Time::parse(sprintf('%02d:%02d', intdiv($slotStart, 60), $slotStart % 60));
            }
        }

        return $slots;
    }

    /**
     * Resolves the [startMinutes, endMinutes] Availability window for a
     * date, preferring a one-off date override over the recurring
     * day-of-week rule. Returns null when the staff member is unavailable
     * (an override with is_available = false, or no rule at all covers
     * that date).
     *
     * @param int $userId The staff member.
     * @param \Cake\I18n\Date $date The date to resolve.
     * @return array{0: int, 1: int}|null
     */
    protected function resolveAvailabilityWindow(int $userId, Date $date): ?array
    {
        // Availabilities is not TenantScopeBehavior-scoped (it has no
        // business_id column - it's scoped indirectly via user_id), so a
        // plain find() is correct here, not find('unscoped').
        $availabilities = TableRegistry::getTableLocator()->get('Availabilities');

        $override = $availabilities->find()
            ->where(['user_id' => $userId, 'date' => $date])
            ->first();

        if ($override !== null) {
            if (!$override->is_available) {
                return null;
            }

            return [$this->toMinutes($override->start_time), $this->toMinutes($override->end_time)];
        }

        $recurring = $availabilities->find()
            ->where(['user_id' => $userId, 'day_of_week' => (int)$date->format('w'), 'is_available' => true])
            ->first();

        if ($recurring === null) {
            return null;
        }

        return [$this->toMinutes($recurring->start_time), $this->toMinutes($recurring->end_time)];
    }

    /**
     * Returns [startMinutes, endMinutes] ranges, each padded by the buffer,
     * for every non-cancelled Booking the staff member has on the date.
     *
     * @param int $userId The staff member.
     * @param \Cake\I18n\Date $date The date to resolve.
     * @return array<array{0: int, 1: int}>
     */
    protected function resolveBusyRanges(int $userId, Date $date): array
    {
        $bookings = TableRegistry::getTableLocator()->get('Bookings');

        $dayStart = new DateTime($date->format('Y-m-d') . ' 00:00:00');
        $dayEnd = new DateTime($date->format('Y-m-d') . ' 23:59:59');

        $existing = $bookings->find('unscoped')
            ->where([
                'user_id' => $userId,
                'status !=' => 'cancelled',
                'start_time <=' => $dayEnd,
                'end_time >=' => $dayStart,
            ])
            ->all();

        $ranges = [];
        foreach ($existing as $booking) {
            $startMinutes = $this->toMinutes(Time::parse($booking->start_time->format('H:i:s'))) - $this->bufferMinutes;
            $endMinutes = $this->toMinutes(Time::parse($booking->end_time->format('H:i:s'))) + $this->bufferMinutes;
            $ranges[] = [$startMinutes, $endMinutes];
        }

        return $ranges;
    }

    /**
     * @param int $slotStart Minutes since midnight.
     * @param int $slotEnd Minutes since midnight.
     * @param array<array{0: int, 1: int}> $busyRanges Buffered busy ranges.
     * @return bool
     */
    protected function overlapsAnyBusyRange(int $slotStart, int $slotEnd, array $busyRanges): bool
    {
        foreach ($busyRanges as [$busyStart, $busyEnd]) {
            if ($slotStart < $busyEnd && $slotEnd > $busyStart) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Cake\I18n\Time $time Time of day.
     * @return int Minutes since midnight.
     */
    protected function toMinutes(Time $time): int
    {
        return $time->getHours() * 60 + $time->getMinutes();
    }
}
