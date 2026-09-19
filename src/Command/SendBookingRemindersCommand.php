<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\DateTime;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;

/**
 * Finds Bookings starting within the next N hours that haven't had a
 * reminder sent yet, emails the guest, and stamps reminder_sent_at.
 *
 * Intended to run every 10-15 minutes via a standard server cron entry
 * (Phase 1 of the brief's reminder design - a polling command, not a
 * queue). Runs across every tenant, not one Business at a time, so it
 * deliberately uses find('unscoped') rather than the session-based
 * TenantScopeBehavior::setTenantId() flow every other part of the app uses.
 */
class SendBookingRemindersCommand extends Command
{
    use MailerAwareTrait;

    /**
     * @return string
     */
    public static function defaultName(): string
    {
        return 'send_booking_reminders';
    }

    /**
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Sends reminder emails for bookings starting within the next N hours.';
    }

    /**
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(static::getDescription())
            ->addOption('hours', [
                'help' => 'How many hours ahead to look for upcoming bookings.',
                'default' => '24',
            ]);
    }

    /**
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $hours = (int)$args->getOption('hours');
        $windowEnd = DateTime::now()->addHours($hours);

        $bookings = TableRegistry::getTableLocator()->get('Bookings');
        $due = $bookings->find('unscoped')
            ->where([
                'reminder_sent_at IS' => null,
                'status !=' => 'cancelled',
                'start_time >=' => DateTime::now(),
                'start_time <=' => $windowEnd,
            ])
            ->contain([
                'Businesses',
                'Services' => fn($query) => $query->find('unscoped'),
                'Customers' => fn($query) => $query->find('unscoped'),
            ])
            ->all();

        $io->out(sprintf('Found %d booking(s) due a reminder.', count($due)));

        $sent = 0;
        foreach ($due as $booking) {
            $this->getMailer('Booking')->send('reminder', [$booking]);

            // Each booking may belong to a different Business - set the
            // tenant to this booking's own business_id right before its
            // save, not once for the whole batch. TenantScopeBehavior then
            // still meaningfully guards against saving a booking under the
            // wrong tenant, rather than being disabled for the whole run.
            $bookings->behaviors()->get('TenantScope')->setTenantId($booking->business_id);
            $booking->set('reminder_sent_at', DateTime::now());
            if ($bookings->save($booking, ['checkRules' => false])) {
                $sent++;
                $io->verbose(sprintf(
                    'Sent reminder for booking #%d (%s).',
                    $booking->id,
                    $booking->customer->email,
                ));
            } else {
                $io->err(sprintf('Could not save reminder_sent_at for booking #%d.', $booking->id));
            }
        }

        $io->out(sprintf('Sent %d reminder(s).', $sent));
    }
}
