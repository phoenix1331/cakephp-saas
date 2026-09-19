<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class AvailabilitiesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                // User 1, recurring Monday (w=1) 09:00-17:00.
                'id' => 1,
                'user_id' => 1,
                'day_of_week' => 1,
                'date' => null,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_available' => true,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                // User 1, one-off override on 2026-02-03 (a Tuesday, no
                // recurring rule) - a half-day, 09:00-12:00.
                'id' => 2,
                'user_id' => 1,
                'day_of_week' => null,
                'date' => '2026-02-03',
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'is_available' => true,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                // User 1, one-off override on 2026-02-02 (a Monday that
                // would otherwise be recurring-available) marking the day
                // off entirely - overrides must win over the recurring rule.
                'id' => 3,
                'user_id' => 2,
                'day_of_week' => 1,
                'date' => null,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'is_available' => true,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 4,
                'user_id' => 2,
                'day_of_week' => null,
                'date' => '2026-02-02',
                'start_time' => '00:00:00',
                'end_time' => '00:00:00',
                'is_available' => false,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
        ];
        parent::init();
    }
}
