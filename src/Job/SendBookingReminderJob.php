<?php
declare(strict_types=1);

namespace App\Job;

use Cake\I18n\DateTime;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\JobInterface;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * Sends a single Booking's reminder email. Dispatched once, at
 * booking-confirmation time, delayed to arrive at the right send time -
 * the Phase 2 replacement for SendBookingRemindersCommand's polling.
 *
 * Like SendBookingRemindersCommand, this job has no session/identity, so
 * it uses find('unscoped') rather than TenantScopeBehavior::setTenantId() -
 * the tenant it needs isn't known until the Booking itself is fetched.
 */
class SendBookingReminderJob implements JobInterface
{
    use MailerAwareTrait;

    /**
     * @param \Cake\Queue\Job\Message $message job message
     * @return string|null
     */
    public function execute(Message $message): ?string
    {
        $bookingId = (int)$message->getArgument('booking_id');

        $bookings = TableRegistry::getTableLocator()->get('Bookings');
        $booking = $bookings->find('unscoped')
            ->where(['id' => $bookingId])
            ->contain([
                'Businesses',
                'Services' => fn($query) => $query->find('unscoped'),
                'Customers' => fn($query) => $query->find('unscoped'),
            ])
            ->first();

        if ($booking === null) {
            // Booking no longer exists - nothing to remind about.
            return Processor::REJECT;
        }

        // The booking may have been cancelled, or already reminded via
        // some other path, between dispatch and this delayed delivery -
        // both are legitimate reasons to skip, not failures.
        if ($booking->status === 'cancelled' || $booking->reminder_sent_at !== null) {
            return Processor::ACK;
        }

        $this->getMailer('Booking')->send('reminder', [$booking]);

        $bookings->behaviors()->get('TenantScope')->setTenantId($booking->business_id);
        $booking->set('reminder_sent_at', DateTime::now());
        $bookings->save($booking, ['checkRules' => false]);

        return Processor::ACK;
    }
}
