<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class PlansFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Solo',
                'staff_limit' => 1,
                'stripe_price_id' => 'price_solo_test',
                'price' => '19.00',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Team',
                'staff_limit' => 5,
                'stripe_price_id' => null,
                'price' => '49.00',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
        ];
        parent::init();
    }
}
