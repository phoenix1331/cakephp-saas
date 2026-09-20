<?php
declare(strict_types=1);

namespace App\Booking;

use Cake\Core\App;
use Cake\Queue\QueueManager;
use Enqueue\Client\Message as ClientMessage;

/**
 * Dispatches a queued job with a genuinely-applied delivery delay.
 *
 * cakephp/queue's QueueManager::push('delay' => ...) accepts the option
 * and silently drops it for the Redis transport. Client\Producer::sendEvent()
 * defaults every message to SCOPE_MESSAGE_BUS, which GenericDriver routes
 * via sendToRouter() - and that method copies the delay onto the transport
 * message's properties but, unlike its sibling sendToProcessor(), never
 * calls Producer::setDeliveryDelay() before sending. RedisProducer only
 * checks getDeliveryDelay() on the transport message, so the message lands
 * in the immediate list regardless of what delay was requested. Confirmed
 * by reading enqueue/enqueue's GenericDriver and enqueue/redis's
 * RedisProducer source directly, not assumed from the (accurate but easy
 * to miss) "not all message brokers accept this" caveat in
 * QueueManager::push()'s own docblock.
 *
 * GenericDriver::sendToProcessor() is the sibling that does apply the
 * delay - and when a message has its topic set but no explicit processor,
 * it resolves to the very same router queue sendToRouter() would have
 * used, so routing behaviour is identical either way. Setting the
 * message's scope to SCOPE_APP is therefore enough to take that code path
 * instead, with no other change to how the message is built or how
 * WorkerCommand consumes it. If cakephp/queue fixes the delay handling in
 * sendToRouter() upstream, this class - and the `delay` argument to
 * push() - stops being necessary.
 */
class DelayedJobDispatcher
{
    /**
     * @param class-string $jobClass The Job class to dispatch.
     * @param array<string, mixed> $data Payload passed to the job.
     * @param int $delaySeconds Seconds to delay delivery. 0 dispatches immediately.
     * @param string $queueConfig The queue config name (`Queue.{name}` in `config/app.php`).
     * @return void
     */
    public static function push(
        string $jobClass,
        array $data,
        int $delaySeconds,
        string $queueConfig = 'default',
    ): void {
        if ($delaySeconds <= 0) {
            QueueManager::push($jobClass, $data, ['config' => $queueConfig]);

            return;
        }

        $class = App::className($jobClass, 'Job', 'Job') ?? $jobClass;
        $config = QueueManager::getConfig($queueConfig);
        $queue = $config['queue'] ?? 'default';

        $message = new ClientMessage([
            'class' => [$class, 'execute'],
            'args' => [$data],
            'data' => $data,
            'requeueOptions' => [
                'config' => $queueConfig,
                'priority' => null,
                'queue' => $queue,
            ],
        ]);
        $message->setDelay($delaySeconds);
        $message->setScope(ClientMessage::SCOPE_APP);

        QueueManager::engine($queueConfig)->sendEvent($queue, $message);
    }
}
