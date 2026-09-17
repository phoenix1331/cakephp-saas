<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CustomersFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'business_id' => 1,
                'name' => 'Alice Customer',
                'email' => 'alice@example.test',
                'phone' => null,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 2,
                'business_id' => 2,
                'name' => 'Bob Customer',
                'email' => 'bob@example.test',
                'phone' => null,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
        ];
        parent::init();
    }
}
