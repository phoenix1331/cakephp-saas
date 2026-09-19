<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'business_id' => 1,
                'email' => 'owner@alpha-hair-studio.test',
                'password' => 'not-a-real-hash',
                'role' => 'owner',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 2,
                'business_id' => 2,
                'email' => 'owner@beta-tutoring.test',
                'password' => 'not-a-real-hash',
                'role' => 'owner',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 3,
                'business_id' => 1,
                'email' => 'staff@alpha-hair-studio.test',
                'password' => 'not-a-real-hash',
                'role' => 'staff',
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
        ];
        parent::init();
    }
}
