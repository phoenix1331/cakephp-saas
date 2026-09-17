<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ServicesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'business_id' => 1,
                'name' => 'Haircut',
                'duration_minutes' => 30,
                'price' => '25.00',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 2,
                'business_id' => 2,
                'name' => 'Maths Tutoring Session',
                'duration_minutes' => 60,
                'price' => '40.00',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
        ];
        parent::init();
    }
}
