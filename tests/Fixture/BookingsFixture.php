<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class BookingsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'business_id' => 1,
                'service_id' => 1,
                'user_id' => 1,
                'customer_id' => 1,
                'start_time' => '2026-02-01 10:00:00',
                'end_time' => '2026-02-01 10:30:00',
                'status' => 'confirmed',
                'reminder_sent_at' => null,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 2,
                'business_id' => 2,
                'service_id' => 2,
                'user_id' => 2,
                'customer_id' => 2,
                'start_time' => '2026-02-01 14:00:00',
                'end_time' => '2026-02-01 15:00:00',
                'status' => 'confirmed',
                'reminder_sent_at' => null,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
        ];
        parent::init();
    }
}
