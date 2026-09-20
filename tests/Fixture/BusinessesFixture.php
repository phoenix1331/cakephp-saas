<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\I18n\DateTime;
use Cake\TestSuite\Fixture\TestFixture;

class BusinessesFixture extends TestFixture
{
    public function init(): void
    {
        // Mid-trial by default (Business::hasAccess() is true) - tests that
        // specifically need a gated/expired Business set trial_ends_at and
        // subscription_status explicitly rather than relying on this default.
        $trialEndsAt = DateTime::now()->addDays(7)->toDateTimeString();

        $this->records = [
            [
                'id' => 1,
                'name' => 'Alpha Hair Studio',
                'slug' => 'alpha-hair-studio',
                'timezone' => 'Europe/London',
                'stripe_customer_id' => null,
                'subscription_status' => null,
                'trial_ends_at' => $trialEndsAt,
                'plan_id' => null,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Beta Tutoring',
                'slug' => 'beta-tutoring',
                'timezone' => 'Europe/London',
                'stripe_customer_id' => null,
                'subscription_status' => null,
                'trial_ends_at' => $trialEndsAt,
                'plan_id' => null,
                'created' => '2026-01-01 09:00:00',
                'modified' => '2026-01-01 09:00:00',
            ],
        ];
        parent::init();
    }
}
