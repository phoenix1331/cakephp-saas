<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ServicesUsersFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'service_id' => 1,
                'user_id' => 1,
            ],
        ];
        parent::init();
    }
}
