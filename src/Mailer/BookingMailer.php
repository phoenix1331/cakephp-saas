<?php
declare(strict_types=1);

namespace App\Mailer;

use App\Booking\IcsBuilder;
use App\Model\Entity\Booking;
use Cake\Mailer\Mailer;

/**
 * Booking mailer.
 */
class BookingMailer extends Mailer
{
    /**
     * Mailer's name.
     *
     * @var string
     */
    public static string $name = 'Booking';

    /**
     * Sends the guest a booking confirmation with an .ics calendar
     * attachment. Expects $booking to already have Business, Service and
     * Customer contained.
     *
     * @param \App\Model\Entity\Booking $booking The confirmed Booking.
     * @return void
     */
    public function confirmation(Booking $booking): void
    {
        $ics = new IcsBuilder(
            uid: sprintf('booking-%d@slotwise.local', $booking->id),
            summary: $booking->service->name . ' at ' . $booking->business->name,
            description: sprintf(
                '%s with %s. Please arrive a few minutes early.',
                $booking->service->name,
                $booking->business->name,
            ),
            start: $booking->start_time,
            end: $booking->end_time,
            location: $booking->business->name,
        );

        $this
            ->setTo($booking->customer->email, $booking->customer->name)
            ->setSubject(sprintf('Your booking with %s is confirmed', $booking->business->name))
            ->setViewVars(compact('booking'))
            ->setAttachments([
                'booking.ics' => [
                    'data' => $ics->build(),
                    'mimetype' => 'text/calendar',
                ],
            ]);
    }
}
