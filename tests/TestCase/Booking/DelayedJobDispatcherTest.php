<?php
declare(strict_types=1);

namespace App\Test\TestCase\Booking;

use App\Booking\DelayedJobDispatcher;
use App\Job\SendBookingReminderJob;
use Cake\Core\Configure;
use Cake\Queue\QueueManager;
use Cake\TestSuite\TestCase;
use Redis;

/**
 * Covers the workaround in DelayedJobDispatcher for cakephp/queue's Redis
 * transport silently ignoring QueueManager::push()'s `delay` option - see
 * that class's docblock for the root cause. These assertions talk to the
 * real Redis instance used by the `default` Queue config, since the bug
 * only reproduces at that transport layer; mocking it would defeat the
 * point of the test.
 */
class DelayedJobDispatcherTest extends TestCase
{
    private Redis $redis;

    public function setUp(): void
    {
        parent::setUp();

        // A plain TestCase never runs Application::bootstrap(), so
        // QueuePlugin never populates QueueManager's config registry from
        // Configure - do that here, the same way the plugin would.
        if (QueueManager::getConfig('default') === null) {
            QueueManager::setConfig('default', Configure::read('Queue.default'));
        }

        $config = QueueManager::getConfig('default');
        $transport = is_array($config['url']) ? $config['url']['transport'] : $config['url'];
        $url = parse_url($transport);

        $this->redis = new Redis();
        $this->redis->connect($url['host'], $url['port']);
        $this->redis->flushDb();
    }

    public function tearDown(): void
    {
        $this->redis->flushDb();
        $this->redis->close();

        parent::tearDown();
    }

    public function testADelayedPushLandsInTheDelayedSetNotTheImmediateQueue(): void
    {
        DelayedJobDispatcher::push(SendBookingReminderJob::class, ['booking_id' => 42], 3600);

        $this->assertSame(0, $this->redis->lLen('enqueue.app.default'));
        $this->assertSame(1, $this->redis->zCard('enqueue.app.default:delayed'));

        $entries = $this->redis->zRange('enqueue.app.default:delayed', 0, -1);
        $payload = json_decode($entries[0], true);

        $this->assertSame(
            ['App\\Job\\SendBookingReminderJob', 'execute'],
            $payload['body'] === null ? null : json_decode($payload['body'], true)['class'],
        );
    }

    public function testTheDelayedSetEntryIsScoredToTheRequestedDeliveryTime(): void
    {
        $before = time();
        DelayedJobDispatcher::push(SendBookingReminderJob::class, ['booking_id' => 42], 100);
        $after = time();

        $scores = $this->redis->zRange('enqueue.app.default:delayed', 0, -1, true);
        $score = (int)array_values($scores)[0];

        $this->assertGreaterThanOrEqual($before + 100, $score);
        $this->assertLessThanOrEqual($after + 100, $score);
    }

    public function testAZeroDelayPushLandsInTheImmediateQueueInstead(): void
    {
        DelayedJobDispatcher::push(SendBookingReminderJob::class, ['booking_id' => 42], 0);

        $this->assertSame(1, $this->redis->lLen('enqueue.app.default'));
        $this->assertSame(0, $this->redis->zCard('enqueue.app.default:delayed'));
    }
}
